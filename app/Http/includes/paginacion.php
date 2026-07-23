<?php
/**
 * Renderiza un bloque de paginación reutilizable.
 *
 * @param int   $page        Página actual (>=1)
 * @param int   $totalPages  Total de páginas (>=1)
 * @param array $params      Parámetros de query a preservar (filtros): ['campo' => 'valor', ...]
 */
function renderPagination(int $page, int $totalPages, array $params = []): void {
    if ($totalPages <= 1) return;

    // Construimos QS base sin 'page' (lo agregamos en cada link)
    unset($params['page']);
    $qsBase = http_build_query($params);

    echo '<div style="margin-top:10px;">';

    // Anterior
    if ($page > 1) {
        $prev = $page - 1;
        echo '<a href="?'.($qsBase ? "$qsBase&" : '').'page='.$prev.'">« Anterior</a> ';
    } else {
        echo '<span style="opacity:.5">« Anterior</span> ';
    }

    // Números
    for ($p = 1; $p <= $totalPages; $p++) {
        if ($p == $page) {
            echo '<strong> '.$p.' </strong>';
        } else {
            echo ' <a href="?'.($qsBase ? "$qsBase&" : '').'page='.$p.'">'.$p.'</a> ';
        }
    }

    // Siguiente
    if ($page < $totalPages) {
        $next = $page + 1;
        echo ' <a href="?'.($qsBase ? "$qsBase&" : '').'page='.$next.'">Siguiente »</a>';
    } else {
        echo ' <span style="opacity:.5">Siguiente »</span>';
    }

    echo '</div>';
}
