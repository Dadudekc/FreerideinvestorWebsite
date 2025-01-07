<?php
/**
 * Admin Tools Page Template
 *
 * @package SimplifiedTradingTheme
 */

// Security check to ensure only authorized users can access this page
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'You do not have sufficient permissions to access this page.', 'simplifiedtradingtheme' ) );
}

// Adjust this path if necessary. If this template is inside your theme, 
// consider a relative path to the plugin or rely on plugin activation checks.
$fintech_plugin_path = WP_PLUGIN_DIR . '/advanced-fintech-engine/advanced-fintech-engine.php';
if ( file_exists( $fintech_plugin_path ) ) {
    require_once $fintech_plugin_path;
} else {
    echo '<div class="error">' . esc_html__( 'Advanced Fintech Engine plugin file not found. Ensure the plugin is installed.', 'simplifiedtradingtheme' ) . '</div>';
    return;
}

// Check if the class is available under a namespace or not
if ( class_exists( 'Advanced_Fintech_Engine' ) ) {
    $fintech = new Advanced_Fintech_Engine();
} else {
    // If namespaced, try:
    // if ( class_exists('\AdvancedFintechEngine\Advanced_Fintech_Engine') ) {
    //     $fintech = new \AdvancedFintechEngine\Advanced_Fintech_Engine();
    // } else {
    //     echo '<div class="error">' . esc_html__( 'Advanced Fintech Engine class not found. Ensure the plugin is active.', 'simplifiedtradingtheme' ) . '</div>';
    //     return;
    // }
    echo '<div class="error">' . esc_html__( 'Advanced Fintech Engine class not found. Ensure the plugin is active.', 'simplifiedtradingtheme' ) . '</div>';
    return;
}

// Fetch portfolio data
$portfolio = method_exists( $fintech, 'get_portfolio_data' ) ? $fintech->get_portfolio_data() : [];
$optimization_results = method_exists( $fintech, 'get_optimization_results' ) ? $fintech->get_optimization_results() : [];
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Fintech Tools Dashboard', 'simplifiedtradingtheme' ); ?></h1>

    <!-- Portfolio Analytics Section -->
    <h2><?php esc_html_e( 'Portfolio Analytics', 'simplifiedtradingtheme' ); ?></h2>
    <?php if ( ! empty( $portfolio ) ) : ?>
        <?php 
        $total_portfolio_value = 0;
        foreach ( $portfolio as $item ) {
            $item_allocation = isset($item['allocation']) ? floatval($item['allocation']) : 0;
            $item_price = isset($item['current_price']) ? floatval($item['current_price']) : 0;
            $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;

            $total_value = $item_allocation * $item_price * $item_quantity;
            $total_portfolio_value += $total_value;
        }
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Symbol', 'simplifiedtradingtheme' ); ?></th>
                    <th><?php esc_html_e( 'Allocation (%)', 'simplifiedtradingtheme' ); ?></th>
                    <th><?php esc_html_e( 'Quantity', 'simplifiedtradingtheme' ); ?></th>
                    <th><?php esc_html_e( 'Current Price ($)', 'simplifiedtradingtheme' ); ?></th>
                    <th><?php esc_html_e( 'Total Value ($)', 'simplifiedtradingtheme' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $portfolio as $item ) : 
                    $item_symbol = isset($item['symbol']) ? $item['symbol'] : '';
                    $item_allocation = isset($item['allocation']) ? floatval($item['allocation']) : 0;
                    $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
                    $item_price = isset($item['current_price']) ? floatval($item['current_price']) : 0;

                    $total_value = $item_allocation * $item_price * $item_quantity;
                ?>
                    <tr>
                        <td><?php echo esc_html( $item_symbol ); ?></td>
                        <td><?php echo esc_html( number_format( $item_allocation * 100, 2 ) ); ?></td>
                        <td><?php echo esc_html( $item_quantity ); ?></td>
                        <td><?php echo esc_html( number_format( $item_price, 2 ) ); ?></td>
                        <td><?php echo esc_html( number_format( $total_value, 2 ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th colspan="4"><?php esc_html_e( 'Total Portfolio Value', 'simplifiedtradingtheme' ); ?></th>
                    <th><?php echo esc_html( '$' . number_format( $total_portfolio_value, 2 ) ); ?></th>
                </tr>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e( 'No portfolio data available.', 'simplifiedtradingtheme' ); ?></p>
    <?php endif; ?>

    <!-- Recent Optimization Results Section -->
    <h2><?php esc_html_e( 'Recent Optimization Results', 'simplifiedtradingtheme' ); ?></h2>
    <?php if ( ! empty( $optimization_results ) ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'simplifiedtradingtheme' ); ?></th>
                    <th><?php esc_html_e( 'Allocations', 'simplifiedtradingtheme' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $optimization_results as $result ) :
                    $opt_date = isset($result['optimization_date']) ? strtotime($result['optimization_date']) : false;
                    $opt_date_display = $opt_date ? date( 'F j, Y, g:i a', $opt_date ) : '';
                    $allocations = isset($result['allocations']) ? json_decode( $result['allocations'], true ) : [];
                ?>
                    <tr>
                        <td><?php echo esc_html( $opt_date_display ); ?></td>
                        <td>
                            <?php 
                            if ( ! empty( $allocations ) && is_array( $allocations ) ) {
                                echo '<ul>';
                                foreach ( $allocations as $symbol => $allocation ) {
                                    echo '<li>' . esc_html( $symbol ) . ': ' . esc_html( number_format( floatval($allocation) * 100, 2 ) ) . '%</li>';
                                }
                                echo '</ul>';
                            } else {
                                esc_html_e( 'No allocations data.', 'simplifiedtradingtheme' );
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e( 'No optimization results available.', 'simplifiedtradingtheme' ); ?></p>
    <?php endif; ?>

    <!-- Portfolio Allocation Chart -->
    <h2><?php esc_html_e( 'Portfolio Allocation Chart', 'simplifiedtradingtheme' ); ?></h2>
    <?php if ( ! empty( $portfolio ) ) : ?>
        <canvas id="portfolioChart" width="400" height="200"></canvas>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('portfolioChart').getContext('2d');
                const portfolioData = <?php echo json_encode(array_map(function($item) {
                    return [
                        'symbol' => isset($item['symbol']) ? $item['symbol'] : '',
                        'value' => isset($item['allocation']) ? floatval($item['allocation']) * 100 : 0
                    ];
                }, $portfolio)); ?>;

                const labels = portfolioData.map(item => item.symbol);
                const data = portfolioData.map(item => item.value);

                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Portfolio Allocation (%)',
                            data: data,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.2)',
                                'rgba(54, 162, 235, 0.2)',
                                'rgba(255, 206, 86, 0.2)',
                                'rgba(75, 192, 192, 0.2)',
                                'rgba(153, 102, 255, 0.2)',
                                'rgba(255, 159, 64, 0.2)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Portfolio Allocation Breakdown'
                            }
                        }
                    },
                });
            });
        </script>
    <?php else : ?>
        <p><?php esc_html_e( 'No portfolio data available to display the chart.', 'simplifiedtradingtheme' ); ?></p>
    <?php endif; ?>
</div>
