<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files are included inside class methods, so render variables are local.
/* @var $analysis */
$score_val   = $analysis['score']      ?? 0;
$score_color = $analysis['color']      ?? '#9ca3af';
$score_label = $analysis['label']      ?? '';
$checks      = $analysis['checks']     ?? [];
$pass_count  = $analysis['pass_count'] ?? 0;
$warn_count  = $analysis['warn_count'] ?? 0;
$fail_count  = $analysis['fail_count'] ?? 0;
$word_count  = $analysis['word_count'] ?? 0;
?>
<div class="gatetouch-analysis">
    <div class="gatetouch-analysis__scoreboard">
        <div class="gatetouch-scoreboard-item">
            <div class="gatetouch-scoreboard-item__num"
                 style="color:<?php echo esc_attr( $score_color ); ?>">
                <?php echo esc_html( $score_val ); ?>
            </div>
            <div class="gatetouch-scoreboard-item__label">SEO Score</div>
        </div>
        <div class="gatetouch-scoreboard-item">
            <div class="gatetouch-scoreboard-item__num" style="color:#10b981"><?php echo esc_html( $pass_count ); ?></div>
            <div class="gatetouch-scoreboard-item__label">Passed</div>
        </div>
        <div class="gatetouch-scoreboard-item">
            <div class="gatetouch-scoreboard-item__num" style="color:#f59e0b"><?php echo esc_html( $warn_count ); ?></div>
            <div class="gatetouch-scoreboard-item__label">Warnings</div>
        </div>
        <div class="gatetouch-scoreboard-item">
            <div class="gatetouch-scoreboard-item__num" style="color:#ef4444"><?php echo esc_html( $fail_count ); ?></div>
            <div class="gatetouch-scoreboard-item__label">Issues</div>
        </div>
        <div class="gatetouch-scoreboard-item">
            <div class="gatetouch-scoreboard-item__num" style="color:#6366f1"><?php echo esc_html( number_format( $word_count ) ); ?></div>
            <div class="gatetouch-scoreboard-item__label">Words</div>
        </div>
    </div>

    <ul class="gatetouch-check-list">
        <?php foreach ( $checks as $check ) :
            $status  = $check['status'];
            $icon_map = [
                'pass' => GateTouch_Helpers::icon( 'check-circle', 16 ),
                'warn' => GateTouch_Helpers::icon( 'alert-triangle', 16 ),
                'fail' => GateTouch_Helpers::icon( 'alert-octagon', 16 ),
            ];
            $icon = $icon_map[ $status ] ?? GateTouch_Helpers::icon( 'info-circle', 16 );
        ?>
        <li class="gatetouch-check-item gatetouch-check-item--<?php echo esc_attr( $status ); ?>">
            <span class="gatetouch-check-item__icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static trusted SVG ?></span>
            <span class="gatetouch-check-item__msg"><?php echo esc_html( $check['message'] ); ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
