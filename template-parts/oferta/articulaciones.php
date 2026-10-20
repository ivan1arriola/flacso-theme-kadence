<?php
/**
 * Presentación compartida de planes articulados en páginas de ofertas y cartas.
 * No representa pagos, inscripciones ni evaluaciones individuales.
 */
if (!defined('ABSPATH')) {
    exit;
}
$planes = isset($args['planes']) && is_array($args['planes']) ? $args['planes'] : [];
if (!$planes) {
    return;
}
?>
<?php foreach ($planes as $plan) :
    $groups = [];
    $by_key = [];
    foreach ((array) ($plan['etapas'] ?? []) as $stage) {
        $level = (int) ($stage['ciclo'] ?? 0);
        if ($level < 1 || empty($stage['clave'])) {
            continue;
        }
        $groups[$level][] = $stage;
        $by_key[(string) $stage['clave']] = $stage;
    }
    if (!$groups) {
        continue;
    }
    ksort($groups, SORT_NUMERIC);
    $conditions = $plan['presentacion'] ?? [];
    ?>
    <section class="flacso-art-public" aria-label="<?php esc_attr_e('Articulación académica por ciclos', 'flacso-uruguay'); ?>">
        <header class="flacso-art-public__header">
            <p class="flacso-art-public__eyebrow"><?php esc_html_e('Formación articulada', 'flacso-uruguay'); ?></p>
            <h2><?php echo esc_html((string) ($plan['nombre'] ?? __('Recorrido académico', 'flacso-uruguay'))); ?></h2>
            <p><?php esc_html_e('Los ciclos se cursan de forma progresiva. Cada etapa superior requiere la aprobación del ciclo anterior, según la trayectoria y la mención.', 'flacso-uruguay'); ?></p>
        </header>
        <div class="flacso-art-public__levels">
            <?php foreach ($groups as $level => $stages) : ?>
                <div class="flacso-art-public__level">
                    <div class="flacso-art-public__level-head">
                        <span class="flacso-art-public__number"><?php echo esc_html((string) $level); ?></span>
                        <h3><?php echo esc_html(sprintf(__('Ciclo %d', 'flacso-uruguay'), (int) $level)); ?></h3>
                        <?php if (count($stages) > 1) : ?>
                            <span class="flacso-art-public__variant"><?php esc_html_e('Recorridos alternativos', 'flacso-uruguay'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flacso-art-public__cards">
                        <?php foreach ($stages as $stage) : ?>
                            <article class="flacso-art-public__card">
                                <h4><?php echo esc_html((string) ($stage['nombre'] ?? '')); ?></h4>
                                <p class="flacso-art-public__degree">
                                    <i class="bi bi-award" aria-hidden="true"></i>
                                    <a href="<?php echo esc_url((string) ($stage['oferta_url'] ?? '')); ?>"><?php echo esc_html((string) ($stage['oferta_titulo'] ?? '')); ?></a>
                                </p>
                                <?php if (!empty($stage['mencion'])) : ?>
                                    <p class="flacso-art-public__mention"><?php echo esc_html(sprintf(__('Mención: %s', 'flacso-uruguay'), (string) $stage['mencion'])); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($stage['descripcion'])) : ?>
                                    <div class="flacso-art-public__description"><?php echo wp_kses_post(wpautop((string) $stage['descripcion'])); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($stage['calendario_url'])) : ?>
                                    <a class="flacso-art-public__calendar" href="<?php echo esc_url((string) $stage['calendario_url']); ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                        <?php esc_html_e('Consultar calendario del ciclo', 'flacso-uruguay'); ?>
                                    </a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($plan['transiciones'])) : ?>
            <div class="flacso-art-public__prerequisites">
                <h3><?php esc_html_e('Requisitos de continuidad', 'flacso-uruguay'); ?></h3>
                <ul>
                    <?php foreach ($plan['transiciones'] as $link) :
                        $origin = $by_key[$link['origen'] ?? ''] ?? null;
                        $target = $by_key[$link['destino'] ?? ''] ?? null;
                        if (!$origin || !$target) {
                            continue;
                        }
                        ?>
                        <li>
                            <?php echo esc_html(sprintf(
                                __('Para cursar %1$s se requiere aprobar %2$s.', 'flacso-uruguay'),
                                (string) $target['nombre'],
                                (string) $origin['nombre']
                            )); ?>
                            <?php if (!empty($link['mencion'])) : ?>
                                <span class="flacso-art-public__mention">
                                    <?php echo esc_html(sprintf(__('Mención habilitante: %s', 'flacso-uruguay'), (string) $link['mencion'])); ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (!empty($conditions['introduccion']) || !empty($conditions['continuidad'])) : ?>
            <div class="flacso-art-public__details">
                <?php if (!empty($conditions['introduccion'])) : ?>
                    <div>
                        <h3><?php esc_html_e('Cómo se integra esta oferta', 'flacso-uruguay'); ?></h3>
                        <?php echo wp_kses_post(wpautop((string) $conditions['introduccion'])); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($conditions['continuidad'])) : ?>
                    <div>
                        <h3><?php esc_html_e('Condiciones para continuar', 'flacso-uruguay'); ?></h3>
                        <?php echo wp_kses_post(wpautop((string) $conditions['continuidad'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endforeach; ?>
