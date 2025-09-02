<?php

/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * Enhanced responsive pagination with intelligent auto-expansion of single gaps
 * and Bootstrap breakpoint-specific visibility controls.
 * 
 * Features:
 * - Responsive display: XS/SM/MD/LG breakpoints with different page counts
 * - Auto-expansion: Single page gaps are automatically filled
 * - Smart dots: Only shown when 2+ pages are hidden
 * - Bootstrap integration: Proper display utility classes
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if (!defined('ABSPATH')) {
	exit;
}

$total   = isset($total) ? $total : wc_get_loop_prop('total_pages');
$current = isset($current) ? $current : wc_get_loop_prop('current_page');
$base    = isset($base) ? $base : esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
$format  = isset($format) ? $format : '';

if ($total <= 1) {
	return;
}

$pagination_links = paginate_links(array(
	'base'      => $base,
	'format'    => $format,
	'add_args'  => false,
	'current'   => max(1, $current),
	'total'     => $total,
	'prev_text' => is_rtl() ? '<i class="fa-sharp fa-light fa-angle-right fa-sm"></i>' : '<i class="fa-sharp fa-light fa-angle-left fa-sm"></i>',
	'next_text' => is_rtl() ? '<i class="fa-sharp fa-light fa-angle-left fa-sm"></i>' : '<i class="fa-sharp fa-light fa-angle-right fa-sm"></i>',
	'type'      => 'array',
	'end_size'  => 1,        // Erste und letzte Seite
	'mid_size'  => 3,        // Erweitert für responsive Anzeige (max für lg screens)
	'show_all'  => false,
));

if (is_array($pagination_links)) {
	// Debug configuration - set to true for debugging, false for production
	$debug_pagination = false;
	
	echo '<nav class="woocommerce-pagination mb" aria-label="Product Pagination">';
	echo '<ul class="pagination">';

	// Filter out WordPress default dots
	$filtered_links = array();
	foreach ($pagination_links as $link) {
		if (strpos($link, 'dots') === false) {
			$filtered_links[] = $link;
		}
	}
	
	// Configuration for responsive breakpoints
	$config = array(
		'xs' => array('mid_size' => 0, 'max_total' => 5),
		'sm' => array('mid_size' => 1, 'max_total' => 7),
		'md' => array('mid_size' => 2, 'max_total' => 9),
		'lg' => array('mid_size' => 3, 'max_total' => 11)
	);
	
	// Extract page data
	$pages = array();
	foreach ($filtered_links as $link) {
		$is_prev = strpos($link, 'prev') !== false;
		$is_next = strpos($link, 'next') !== false;
		$is_current = strpos($link, 'current') !== false;
		
		if ($is_prev || $is_next) {
			$pages[] = array(
				'type' => $is_prev ? 'prev' : 'next',
				'link' => $link,
				'page_num' => null
			);
		} else {
			$page_num = null;
			if (preg_match('/href="[^"]*\/(\d+)\/"/', $link, $matches)) {
				$page_num = intval($matches[1]);
			} elseif ($is_current) {
				$page_num = $current;
			}
			
			if ($page_num) {
				$pages[] = array(
					'type' => 'page',
					'link' => $link,
					'page_num' => $page_num,
					'is_current' => $is_current,
					'is_first' => ($page_num === 1),
					'is_last' => ($page_num === $total)
				);
			}
		}
	}
	
	// Sort pages by page number
	usort($pages, function($a, $b) {
		if ($a['type'] !== 'page' || $b['type'] !== 'page') return 0;
		return $a['page_num'] - $b['page_num'];
	});
	
	// Function to auto-expand single gaps
	function auto_expand_single_gaps($visible_pages, $total, $debug = false) {
		$expanded = $visible_pages;
		
		for ($i = 0; $i < count($visible_pages) - 1; $i++) {
			$current_page = $visible_pages[$i];
			$next_page = $visible_pages[$i + 1];
			
			// If gap is exactly 1 page, fill it
			if ($next_page - $current_page === 2) {
				$missing_page = $current_page + 1;
				$expanded[] = $missing_page;
				if ($debug) {
					echo "<!-- DEBUG: Gap found between $current_page and $next_page, adding $missing_page -->";
				}
			}
		}
		
		// Remove duplicates and sort
		$expanded = array_unique($expanded);
		sort($expanded);
		
		return $expanded;
	}
	
	// Function to get visible pages for a breakpoint
	function get_visible_pages($current, $total, $breakpoint_config, $debug = false) {
		// If few pages: show all
		if ($total <= $breakpoint_config['max_total']) {
			return range(1, $total);
		}
		
		$visible = array();
		$mid_size = $breakpoint_config['mid_size'];
		
		// Always include first and last
		$visible[] = 1;
		if ($total > 1) {
			$visible[] = $total;
		}
		
		// Always include current
		$visible[] = $current;
		
		// Add pages around current based on mid_size
		for ($i = $current - $mid_size; $i <= $current + $mid_size; $i++) {
			if ($i >= 1 && $i <= $total) {
				$visible[] = $i;
			}
		}
		
		// Remove duplicates and sort
		$visible = array_unique($visible);
		sort($visible);
		
		// Auto-expand single gaps immediately - but run multiple times for chain expansion
		$expanded = $visible;
		$prev_count = 0;
		
		// Keep expanding until no more changes (handles chain gaps)
		while (count($expanded) !== $prev_count) {
			$prev_count = count($expanded);
			$expanded = auto_expand_single_gaps($expanded, $total, $debug);
		}
		
		return $expanded;
	}
	
	// Function to count hidden pages between two visible pages
	function count_hidden_between($visible_pages, $start, $end) {
		if (!$start || !$end || $end <= $start) return 0;
		
		$hidden_count = 0;
		for ($i = $start + 1; $i < $end; $i++) {
			if (!in_array($i, $visible_pages)) {
				$hidden_count++;
			}
		}
		return $hidden_count;
	}
	
	// Get visible pages for each breakpoint
	$visible_xs = get_visible_pages($current, $total, $config['xs'], $debug_pagination);
	$visible_sm = get_visible_pages($current, $total, $config['sm'], $debug_pagination);
	$visible_md = get_visible_pages($current, $total, $config['md'], $debug_pagination);
	$visible_lg = get_visible_pages($current, $total, $config['lg'], $debug_pagination);
	
	// Debug output (only when debug is enabled)
	if ($debug_pagination) {
		echo "<!-- DEBUG: Current=$current, Total=$total -->";
		echo "<!-- XS: " . implode(',', $visible_xs) . " -->";
		echo "<!-- SM: " . implode(',', $visible_sm) . " -->";
		echo "<!-- MD: " . implode(',', $visible_md) . " -->";
		echo "<!-- LG: " . implode(',', $visible_lg) . " -->";
	}
	
	// Render pagination
	
	// First render prev/next if they exist
	foreach ($pages as $page) {
		if ($page['type'] === 'prev') {
			echo '<li class="page-item">' . str_replace('page-numbers', 'page-link', $page['link']) . '</li>';
			break;
		}
	}
	
	// Get all unique page numbers that appear in any breakpoint
	$all_possible_pages = array_unique(array_merge($visible_xs, $visible_sm, $visible_md, $visible_lg));
	sort($all_possible_pages);
	
	// Render all page numbers
	for ($p = 1; $p <= $total; $p++) {
		// Skip if this page is not visible in any breakpoint
		if (!in_array($p, $all_possible_pages)) {
			continue;
		}
		
		$active_class = ($p === $current) ? ' active' : '';
		
		// Calculate display classes
		$classes = array();
		
		$show_xs = in_array($p, $visible_xs);
		$show_sm = in_array($p, $visible_sm);
		$show_md = in_array($p, $visible_md);
		$show_lg = in_array($p, $visible_lg);
		
		// Build Bootstrap display classes correctly
		if (!$show_xs) {
			$classes[] = 'd-none';
		}
		if ($show_sm && !$show_xs) {
			$classes[] = 'd-sm-block';
		}
		if (!$show_sm && $show_xs) {
			$classes[] = 'd-sm-none';
		}
		if ($show_md && !$show_sm) {
			$classes[] = 'd-md-block';
		}
		if (!$show_md && $show_sm) {
			$classes[] = 'd-md-none';
		}
		if ($show_lg && !$show_md) {
			$classes[] = 'd-lg-block';
		}
		if (!$show_lg && $show_md) {
			$classes[] = 'd-lg-none';
		}
		
		$display_class = implode(' ', $classes);
		
		// Handle first page with dots after
		if ($p === 1) {
			if ($p === $current) {
				echo '<li class="page-item active ' . $display_class . '"><span aria-current="page" class="page-link current">' . $p . '</span></li>';
			} else {
				// Find original link from pages array or construct
				$page_link = null;
				foreach ($pages as $page) {
					if ($page['type'] === 'page' && $page['page_num'] === $p) {
						$page_link = $page['link'];
						break;
					}
				}
			if (!$page_link) {
				// Construct link if not found
				$page_url = str_replace('%#%', $p, $base); // <-- KORREKTUR
				$page_link = '<a class="page-link" href="' . esc_url($page_url) . '">' . $p . '</a>';
			}
				echo '<li class="page-item ' . $display_class . '">' . str_replace('page-numbers', 'page-link', $page_link) . '</li>';
			}
			
			// Find next visible page for each breakpoint
			$next_xs = null; $next_sm = null; $next_md = null; $next_lg = null;
			
			foreach ($visible_xs as $vp) { if ($vp > 1) { $next_xs = $vp; break; } }
			foreach ($visible_sm as $vp) { if ($vp > 1) { $next_sm = $vp; break; } }
			foreach ($visible_md as $vp) { if ($vp > 1) { $next_md = $vp; break; } }
			foreach ($visible_lg as $vp) { if ($vp > 1) { $next_lg = $vp; break; } }
			
			// Count hidden pages (need 2+ hidden pages for dots)
			$hidden_xs = count_hidden_between($visible_xs, 1, $next_xs);
			$hidden_sm = count_hidden_between($visible_sm, 1, $next_sm);
			$hidden_md = count_hidden_between($visible_md, 1, $next_md);
			$hidden_lg = count_hidden_between($visible_lg, 1, $next_lg);
			
			// Only show dots if 2+ pages are hidden
			$show_dots_xs = $hidden_xs >= 2;
			$show_dots_sm = $hidden_sm >= 2;
			$show_dots_md = $hidden_md >= 2;
			$show_dots_lg = $hidden_lg >= 2;
			
			// Only render dots if needed in at least one breakpoint
			if ($show_dots_xs || $show_dots_sm || $show_dots_md || $show_dots_lg) {
				$dot_classes = array();
				
				// Start with hide all
				if (!$show_dots_xs) {
					$dot_classes[] = 'd-none';
				}
				
				// SM
				if ($show_dots_sm && !$show_dots_xs) {
					$dot_classes[] = 'd-sm-inline-block';
				} elseif (!$show_dots_sm && $show_dots_xs) {
					$dot_classes[] = 'd-sm-none';
				}
				
				// MD
				if ($show_dots_md && !$show_dots_sm) {
					$dot_classes[] = 'd-md-inline-block';
				} elseif (!$show_dots_md && $show_dots_sm) {
					$dot_classes[] = 'd-md-none';
				}
				
				// LG
				if ($show_dots_lg && !$show_dots_md) {
					$dot_classes[] = 'd-lg-inline-block';
				} elseif (!$show_dots_lg && $show_dots_md) {
					$dot_classes[] = 'd-lg-none';
				}
				
				$dot_display = implode(' ', $dot_classes);
				echo '<li class="page-item ' . $dot_display . '"><span class="page-link dots">…</span></li>';
			}
		}
		// Handle last page with dots before
		elseif ($p === $total) {
			// Find previous visible page for each breakpoint
			$prev_xs = null; $prev_sm = null; $prev_md = null; $prev_lg = null;
			
			foreach (array_reverse($visible_xs) as $vp) { if ($vp < $total) { $prev_xs = $vp; break; } }
			foreach (array_reverse($visible_sm) as $vp) { if ($vp < $total) { $prev_sm = $vp; break; } }
			foreach (array_reverse($visible_md) as $vp) { if ($vp < $total) { $prev_md = $vp; break; } }
			foreach (array_reverse($visible_lg) as $vp) { if ($vp < $total) { $prev_lg = $vp; break; } }
			
			// Count hidden pages (need 2+ hidden pages for dots)
			$hidden_xs = count_hidden_between($visible_xs, $prev_xs, $total);
			$hidden_sm = count_hidden_between($visible_sm, $prev_sm, $total);
			$hidden_md = count_hidden_between($visible_md, $prev_md, $total);
			$hidden_lg = count_hidden_between($visible_lg, $prev_lg, $total);
			
			// Only show dots if 2+ pages are hidden
			$show_dots_xs = $hidden_xs >= 2;
			$show_dots_sm = $hidden_sm >= 2;
			$show_dots_md = $hidden_md >= 2;
			$show_dots_lg = $hidden_lg >= 2;
			
			// Only render dots if needed in at least one breakpoint
			if ($show_dots_xs || $show_dots_sm || $show_dots_md || $show_dots_lg) {
				$dot_classes = array();
				
				// Start with hide all
				if (!$show_dots_xs) {
					$dot_classes[] = 'd-none';
				}
				
				// SM
				if ($show_dots_sm && !$show_dots_xs) {
					$dot_classes[] = 'd-sm-inline-block';
				} elseif (!$show_dots_sm && $show_dots_xs) {
					$dot_classes[] = 'd-sm-none';
				}
				
				// MD
				if ($show_dots_md && !$show_dots_sm) {
					$dot_classes[] = 'd-md-inline-block';
				} elseif (!$show_dots_md && $show_dots_sm) {
					$dot_classes[] = 'd-md-none';
				}
				
				// LG
				if ($show_dots_lg && !$show_dots_md) {
					$dot_classes[] = 'd-lg-inline-block';
				} elseif (!$show_dots_lg && $show_dots_md) {
					$dot_classes[] = 'd-lg-none';
				}
				
				$dot_display = implode(' ', $dot_classes);
				echo '<li class="page-item ' . $dot_display . '"><span class="page-link dots">…</span></li>';
			}
			
			if ($p === $current) {
				echo '<li class="page-item active ' . $display_class . '"><span aria-current="page" class="page-link current">' . $p . '</span></li>';
			} else {
				// Find original link from pages array or construct
				$page_link = null;
				foreach ($pages as $page) {
					if ($page['type'] === 'page' && $page['page_num'] === $p) {
						$page_link = $page['link'];
						break;
					}
				}
				if (!$page_link) {
					// Construct link if not found
					$page_url = str_replace('%#%', $p, $base); // <-- KORREKTUR
					$page_link = '<a class="page-link" href="' . esc_url($page_url) . '">' . $p . '</a>';
				}
				echo '<li class="page-item ' . $display_class . '">' . str_replace('page-numbers', 'page-link', $page_link) . '</li>';
			}
		}
		// Regular middle page
		else {
			if ($p === $current) {
				echo '<li class="page-item active ' . $display_class . '"><span aria-current="page" class="page-link current">' . $p . '</span></li>';
			} else {
				// Find original link from pages array or construct
				$page_link = null;
				foreach ($pages as $page) {
					if ($page['type'] === 'page' && $page['page_num'] === $p) {
						$page_link = $page['link'];
						break;
					}
				}
			if (!$page_link) {
				// Construct link if not found
				$page_url = str_replace('%#%', $p, $base); // <-- KORREKTUR
				$page_link = '<a class="page-link" href="' . esc_url($page_url) . '">' . $p . '</a>';
			}
				echo '<li class="page-item ' . $display_class . '">' . str_replace('page-numbers', 'page-link', $page_link) . '</li>';
			}
		}
	}
	
	// Render next button if it exists
	foreach ($pages as $page) {
		if ($page['type'] === 'next') {
			echo '<li class="page-item">' . str_replace('page-numbers', 'page-link', $page['link']) . '</li>';
			break;
		}
	}

	echo '</ul>';
	echo '</nav>';
}
