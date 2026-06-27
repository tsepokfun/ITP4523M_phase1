<?php
/**
 * Shared sort utilities for table column headers.
 * Include with: require_once __DIR__.'/../../sort_utils.php'; (adjust depth)
 */

/**
 * Parse and validate sort/get parameters.
 *
 * @param array  $allowed  Map of column_key => label (whitelist).
 * @param string $default  Default column key.
 * @return array [$sort_col, $sort_order]  Validated, safe for SQL ORDER BY.
 */
function get_sort_params($allowed, $default) {
    $col = isset($_GET['sort']) && array_key_exists($_GET['sort'], $allowed)
        ? $_GET['sort']
        : $default;
    $ord = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC'
        ? 'ASC'
        : 'DESC';
    return [$col, $ord];
}

/**
 * Build SQL ORDER BY clause from validated sort params.
 *
 * @param string $col   Column key (already validated).
 * @param string $ord   ASC or DESC.
 * @return string  e.g. "ORDER BY col ASC"
 */
function sort_clause($col, $ord) {
    return "ORDER BY {$col} {$ord}";
}

/**
 * Render a sortable <th> with clickable link and arrow indicator.
 *
 * @param string $key    Column key (must match key in $allowed map).
 * @param string $label  Display label for the column header.
 * @param string $current_sort  Currently active sort column key.
 * @param string $current_order Currently active sort order (ASC/DESC).
 * @return string HTML <th> element.
 */
function sortable_th($key, $label, $current_sort, $current_order) {
    $is_active = ($key === $current_sort);
    // Determine next order: flip if active, else default ASC
    $next_order = $is_active ? ($current_order === 'ASC' ? 'DESC' : 'ASC') : 'ASC';

    // Arrow indicator
    if ($is_active) {
        $arrow = $current_order === 'ASC' ? ' ▲' : ' ▼';
        $arrow_class = ' sort-arrow active';
    } else {
        $arrow = ' ⇅';
        $arrow_class = ' sort-arrow';
    }

    // Build query string preserving existing params (except sort/order)
    $qs = $_GET;
    $qs['sort'] = $key;
    $qs['order'] = $next_order;
    $href = '?' . http_build_query($qs);

    return '<th><a class="sort-link" href="' . htmlspecialchars($href) . '">'
         . htmlspecialchars($label)
         . '<span class="' . $arrow_class . '">' . $arrow . '</span>'
         . '</a></th>';
}

/**
 * Return just the arrow indicator HTML (for headers that use custom <th> styling).
 *
 * @param string $key    Column key.
 * @param string $current_sort  Currently active sort column.
 * @param string $current_order Currently active sort order.
 * @return string HTML arrow span.
 */
function sort_arrow($key, $current_sort, $current_order) {
    if ($key !== $current_sort) {
        return ' <span class="sort-arrow">⇅</span>';
    }
    $arrow = $current_order === 'ASC' ? '▲' : '▼';
    return ' <span class="sort-arrow active">' . $arrow . '</span>';
}
