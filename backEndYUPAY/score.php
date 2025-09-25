<?php
require_once __DIR__ . '/transacciones.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_POST['dni_usuario'])) {
    echo json_encode(["error" => "Falta el parámetro dni_usuario"]);
    exit;
}

$dni_usuario = $_POST['dni_usuario'];

$transacciones = new Transaccion();
$data = $transacciones->getPorUsuario($dni_usuario);

if (!$data || isset($data['error'])) {
    echo json_encode(["error" => "No se pudieron obtener transacciones"]);
    exit;
}

// --- Recomendaciones personalizadas ---
$recomendaciones = [];

// --- Calcular métricas ---

$ingresos_por_mes = [];
$gastos_por_mes = [];
$total_ingresos = 0;
$total_gastos = 0;
$meses_deficit = 0;

foreach ($data as $t) {
    $mes = date("Y-m", strtotime($t['fecha']));
    if ($t['tipo'] === 'ingreso') {
        $ingresos_por_mes[$mes] = ($ingresos_por_mes[$mes] ?? 0) + $t['monto'];
        $total_ingresos += $t['monto'];
    } else {
        $gastos_por_mes[$mes] = ($gastos_por_mes[$mes] ?? 0) + $t['monto'];
        $total_gastos += $t['monto'];
    }
}

/* Ratio de ahorro 
Es una medida que indica qué parte de tus ingresos logras ahorrar en lugar de gastar.

¿Qué significa el resultado?

0.0 → 0.2 (0% - 20%): Estás ahorrando poco, deberías controlar más los gastos.

0.2 → 0.5 (20% - 50%): Tienes un buen nivel de ahorro, pero todavía se puede mejorar.

0.5 → 1 (50% - 100%): Excelente, ahorras una gran parte de tus ingresos.

Cuanto más alto sea el ratio, mejor estás administrando tu dinero porque ahorras más de lo que gastas.

*/

$ratio_ahorro = ($total_ingresos > 0) ? max(0, ($total_ingresos - $total_gastos) / $total_ingresos) : 0;

if ($ratio_ahorro < 0.2) {
    $recomendaciones[] = "Intenta ahorrar al menos el 20% de tus ingresos cada mes.";
} elseif ($ratio_ahorro < 0.5) {
    $recomendaciones[] = "Vas bien, pero podrías aumentar un poco tu nivel de ahorro.";
} else {
    $recomendaciones[] = "¡Excelente! Tienes un buen hábito de ahorro.";
}

/* Estabilidad de ingresos (desviación estándar)

Mide qué tan regulares y predecibles son tus ingresos cada mes.
Si cada mes recibes más o menos lo mismo → alta estabilidad.
Si algunos meses ganas mucho y otros muy poco → baja estabilidad.

¿Cómo se calcula?

1) Se sacan los ingresos de cada mes → $ingresos_mensuales.
Ejemplo: [1000, 950, 1200, 1100]

2) Se calcula la media (promedio) → media = suma de ingresos / numero de meses

3) Se calcula la desviación estándar → mide cuánto se alejan los ingresos de la media

    Si es baja: ingresos parecidos todos los meses.

    Si es alta: ingresos muy variables.

4) Se normaliza la estabilidad → $estabilidad = 1 - ($desviacion / $media);

    Si la desviación es cero (todos los ingresos iguales) → estabilidad = 1 (perfecta).

    Si la desviación es muy grande → estabilidad baja, cercana a 0.

¿Qué significa el resultado?

< 0.4 → Muy variable → tus ingresos cambian mucho, poco seguros.

0.4 - 0.7 → Moderada → tienes cierta estabilidad, pero hay meses irregulares.

> 0.7 → Alta → ingresos bastante estables y predecibles.

La fórmula convierte la variabilidad de ingresos en un número entre 0 y 1, donde 1 = ingresos estables y 0 = muy inestables.
*/

$ingresos_mensuales = array_values($ingresos_por_mes);
$media = (count($ingresos_mensuales) > 0) ? array_sum($ingresos_mensuales) / count($ingresos_mensuales) : 0;
$desviacion = 0;
if (count($ingresos_mensuales) > 1) {
    foreach ($ingresos_mensuales as $val) {
        $desviacion += pow($val - $media, 2);
    }
    $desviacion = sqrt($desviacion / (count($ingresos_mensuales) - 1));
}
$estabilidad = ($media > 0) ? max(0, 1 - ($desviacion / $media)) : 0;

if ($estabilidad < 0.4) {
    $recomendaciones[] = "Tus ingresos son muy variables. Intenta buscar ingresos más estables.";
} elseif ($estabilidad < 0.7) {
    $recomendaciones[] = "Tus ingresos tienen cierta estabilidad, pero aún puedes mejorar.";
} else {
    $recomendaciones[] = "Tienes ingresos bastante estables. ¡Sigue así!";
}

/* Saldo acumulado 

Saldo acumulado = Ingresos totales – Gastos totales.

Negativo → gastas más de lo que ganas (alerta).

Positivo bajo (<10%) → te queda poco margen, intenta gastar menos.

Positivo alto (>10%) → buena gestión, estás generando ahorro.

Es un indicador de cuánto te queda realmente después de cubrir tus gastos.

*/

$saldo_acumulado = $total_ingresos - $total_gastos;
$saldo_normalizado = ($total_ingresos > 0) ? min(1, $saldo_acumulado / $total_ingresos) : 0;

if ($saldo_acumulado < 0) {
    $recomendaciones[] = "Estás gastando más de lo que ingresas. Ajusta tus gastos.";
} elseif ($saldo_acumulado < ($total_ingresos * 0.1)) {
    $recomendaciones[] = "Tu saldo es positivo pero bajo, intenta gastar menos.";
} else {
    $recomendaciones[] = "Tienes un buen saldo acumulado. Mantén el control de tus gastos.";
}

/* Meses en déficit
Este bloque mide cuántos meses gastas más de lo que ingresas:

1) Recorre cada mes.

2) Si los gastos > ingresos, suma un mes en déficit.

3) Según el número de meses en déficit:

    Más de 2 → varios meses gastando de más → alerta fuerte.

    1 o 2 → algunos meses descontrolados → revisar presupuesto.

    0 → nunca en déficit → buena señal.

Indica la frecuencia con la que tus gastos superan tus ingresos mensuales y da recomendaciones para mantener un mejor equilibrio.
*/
foreach ($ingresos_por_mes as $mes => $ingresos) {
    $gastos = $gastos_por_mes[$mes] ?? 0;
    if ($gastos > $ingresos) {
        $meses_deficit++;
    }
}

if ($meses_deficit > 2) {
    $recomendaciones[] = "Has tenido varios meses en déficit. Intenta controlar tus gastos mensuales.";
} elseif ($meses_deficit > 0) {
    $recomendaciones[] = "En algunos meses gastaste más de lo que ingresaste. Revisa tu presupuesto.";
} else {
    $recomendaciones[] = "No has tenido meses en déficit. ¡Muy bien!";
}

/* --- Distribución por categorías (gasto e ingreso) ---

Este bloque calcula la diversificación de tus gastos y ingresos usando el índice HHI (Herfindahl-Hirschman Index):

Diversificación de gastos mide si tu dinero se reparte entre distintas categorías o se concentra en pocas.

Diversificación de ingresos mide si dependes de una sola fuente de ingresos o de varias.

¿Como se calcula?

Se obtiene cuánto gastas/ingresas por categoría.

Ej: comida, transporte, ocio (en gastos).

Ej: sueldo, inversiones, extras (en ingresos).

Se calcula el HHI:

Para cada categoría → proporción p = (monto categoría / total).

Se suma 𝑝^2.

    Resultado cercano a 1 = alta concentración (todo en una categoría).

    Resultado más bajo = más diversificación.

Recomendaciones:

HHI > 0.5 en gastos → gastas sobre todo en 1–2 cosas → recomienda equilibrar.

HHI ≤ 0.5 en gastos → diversificación saludable.

HHI > 0.5 en ingresos → dependes casi de una única fuente (ej: solo salario).

HHI ≤ 0.5 en ingresos → buena diversificación de fuentes.
*/

$gastosCat = $transacciones->getGastosPorCategoria($dni_usuario);
$gasto_total = max(1, $total_gastos);
$hhi_gasto = 0;
foreach ($gastosCat as $row) {
    $p = $row['total'] / $gasto_total;
    $hhi_gasto += $p * $p;
}


$ingresosCat = $transacciones->getIngresosPorCategoria($dni_usuario);
$ingreso_total = max(1, $total_ingresos);
$hhi_ingreso = 0;
foreach ($ingresosCat as $row) {
    $p = $row['total'] / $ingreso_total;
    $hhi_ingreso += $p * $p;
}

// Diversificación de gastos
if ($hhi_gasto > 0.5) {
    $recomendaciones[] = "Tus gastos se concentran en pocas categorías. Intenta equilibrarlos.";
} else {
    $recomendaciones[] = "Tienes un gasto diversificado. Eso ayuda a un mejor control financiero.";
}

// Diversificación de ingresos
if ($hhi_ingreso > 0.5) {
    $recomendaciones[] = "Dependes mucho de una sola fuente de ingresos. Considera diversificar.";
} else {
    $recomendaciones[] = "Tus ingresos están diversificados. ¡Buen trabajo!";
}

// --- Regularidad de uso del sistema ---
// Contamos cuántos días distintos tiene movimientos registrados
$dias_con_movimientos = [];
foreach ($data as $t) {
    $dia = date("Y-m-d", strtotime($t['fecha']));
    $dias_con_movimientos[$dia] = true;
}
$numero_dias = count($dias_con_movimientos);

// Total de días desde la primera hasta la última transacción
$fechas = array_column($data, 'fecha');
$min_fecha = min($fechas);
$max_fecha = max($fechas);
$dias_totales = (strtotime($max_fecha) - strtotime($min_fecha)) / (60 * 60 * 24) + 1;

// Regularidad = días con movimientos / días totales
$regularidad = ($dias_totales > 0) ? round($numero_dias / $dias_totales, 2) : 0;

// Recomendaciones según regularidad
if ($regularidad < 0.3) {
    $recomendaciones[] = "Registra tus transacciones con más frecuencia para llevar un mejor control.";
} elseif ($regularidad < 0.7) {
    $recomendaciones[] = "Estás usando el sistema de forma moderada. Intenta registrar más seguido.";
} else {
    $recomendaciones[] = "¡Excelente! Usas el sistema de forma constante y responsable.";
}

/* 
-----------------------------------------------------
Distribución de pesos para el Score (0 - 100)

1) Ratio de ahorro → hasta 30 puntos
   - Cuanto mayor sea el ahorro respecto a ingresos, más puntúa.

2) Estabilidad de ingresos → hasta 20 puntos
   - Ingresos más regulares = más puntos.

3) Saldo acumulado → hasta 20 puntos
   - Saldo positivo alto = más puntos, saldo negativo = resta.

4) Meses en déficit → penalización
   - Cada mes en déficit resta 1.5 puntos.

5) Diversificación de categorías (HHI)
   - Gastos concentrados → -5 puntos.
   - Gastos muy diversificados → +3 puntos.
   - Ingresos concentrados → -4 puntos.
   - Ingresos diversificados → +2 puntos.

6) Regularidad de uso → hasta 10 puntos extra
   - Usuario constante en registrar transacciones = más puntos.

El score final siempre se ajusta al rango [0, 100].
-----------------------------------------------------
*/

$score = 0;
$score += $ratio_ahorro * 30;         // Ahorro aporta hasta 30 pts
$score += $estabilidad * 20;          // Estabilidad hasta 20 pts
$score += $saldo_normalizado * 20;    // Saldo hasta 20 pts
$score -= $meses_deficit * 1.5;       // Penalización por déficit

// Penalizar/bonificar concentración de categorías
if ($hhi_gasto > 0.5) $score -= 5;
elseif ($hhi_gasto < 0.2) $score += 3;

if ($hhi_ingreso > 0.5) $score -= 4;
else $score += 2;

// --- Aportación de la regularidad (0 a 10 puntos) ---
$score += $regularidad * 10;

// Limitar entre 0 y 100
$score = max(0, min(100, round($score)));


// --- Devolver datos y recomendaciones ---
echo json_encode([
    "dni_usuario" => $dni_usuario,
    "ratio_ahorro" => round($ratio_ahorro, 2),
    "estabilidad_ingresos" => round($estabilidad, 2),
    "saldo_acumulado" => $saldo_acumulado,
    "meses_deficit" => $meses_deficit,
    "hhi_gasto" => round($hhi_gasto, 3),
    "hhi_ingreso" => round($hhi_ingreso, 3),
    "score" => $score,
    "recomendaciones" => $recomendaciones
], JSON_PRETTY_PRINT);

?>
