<?php
$conexion = mysqli_connect('tostao', 'localhost', 'contraseña', 'caol');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener consultores seleccionados
    $consultoresSeleccionados = isset($_POST['consultores']) ? $_POST['consultores'] : array();

    foreach ($consultoresSeleccionados as $consultor) {
        // Consulta para obtener facturas relacionadas al consultor
        $consultaFacturas = "SELECT SUM(VALOR) AS VALOR,
                                  SUM(TOTAL_IMP_INC) AS TOTAL_IMP_INC
                             FROM CAO_FATURA
                            WHERE CO_USUARIO = $consultor
                              AND MONTH(DATA_EMISSAO) = MONTH(CURRENT_DATE())";

        $resultadoFacturas = mysqli_query($conexion, $consultaFacturas);
        $filaFacturas = mysqli_fetch_assoc($resultadoFacturas);

        // Cálculos
        $receitaLiquida = $filaFacturas['VALOR'] - ($filaFacturas['VALOR'] * $filaFacturas['TOTAL_IMP_INC'] / 100);
        
        $consultaCustoFixo = "SELECT BRUT_SALARIO FROM CAO_SALARIO WHERE CO_USUARIO = $consultor";
        $resultadoCustoFixo = mysqli_query($conexion, $consultaCustoFixo);
        $filaCustoFixo = mysqli_fetch_assoc($resultadoCustoFixo);
        $custoFixo = $filaCustoFixo['BRUT_SALARIO'];

        $consultaComissao = "SELECT SUM((VALOR - (VALOR * TOTAL_IMP_INC / 100)) * COMISSAO_CN) AS COMISSAO
                              FROM CAO_FATURA
                             WHERE CO_USUARIO = $consultor
                               AND MONTH(DATA_EMISSAO) = MONTH(CURRENT_DATE)";

        $resultadoComissao = mysqli_query($conexion, $consultaComissao);
        $filaComissao = mysqli_fetch_assoc($resultadoComissao);
        $comissao = $filaComissao['COMISSAO'];

        $lucro = $receitaLiquida - ($custoFixo + $comissao);

        // Mostrar resultados
        echo '<h2>Reporte para Consultor ' . $consultor . '</h2>';
        echo '<p>Receita Líquida: $' . $receitaLiquida . '</p>';
        echo '<p>Custo Fixo: $' . $custoFixo . '</p>';
        echo '<p>Comissão: $' . $comissao . '</p>';
        echo '<p>Lucro: $' . $lucro . '</p>';
        echo '<hr>';
    }
}

mysqli_close($conexion);
?>
