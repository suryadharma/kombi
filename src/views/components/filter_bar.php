<?php
/**
 * Generic filter bar component.
 *
 * Expected $filterConfig structure:
 * [
 *     'id' => 'formId',
 *     'method' => 'GET',
 *     'action' => '/path',
 *     'fields' => [
 *         [
 *             'type' => 'select'|'text'|'search'|'date'|'number'|'custom'|'hidden',
 *             'name' => 'field_name',
 *             'label' => 'Label',
 *             'value' => 'current value',
 *             'placeholder' => 'Placeholder',
 *             'options' => [
 *                 ['value' => '', 'label' => 'Semua'],
 *             ],
 *             'col' => 3,
 *             'attrs' => ['data-example' => '1'],
 *             'auto_submit' => true,
 *             'html' => '<div>Custom</div>', // for custom type
 *         ],
 *     ],
 *     'actions' => [
 *         ['type' => 'submit', 'label' => 'Cari', 'icon' => 'fas fa-search', 'variant' => 'primary'],
 *         ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/path'],
 *     ],
 *     'extra' => '<div>Additional content under fields</div>',
 * ];
 */

$filterConfig = $filterConfig ?? null;
if (!$filterConfig || !is_array($filterConfig)) {
    return;
}

$formId = $filterConfig['id'] ?? ('filterForm_' . uniqid());
$method = strtoupper($filterConfig['method'] ?? 'GET');
$action = $filterConfig['action'] ?? '';
$fields = $filterConfig['fields'] ?? [];
$actions = $filterConfig['actions'] ?? [];
$extra = $filterConfig['extra'] ?? null;

$autoSubmit = false;
$hiddenFields = [];
foreach ($fields as $field) {
    if (($field['type'] ?? '') === 'hidden') {
        $hiddenFields[] = $field;
    }
    if (!empty($field['auto_submit'])) {
        $autoSubmit = true;
    }
}
?>

<div class="card mb-3 filter-card">
    <div class="card-body">
        <form id="<?= htmlspecialchars($formId) ?>"
              method="<?= htmlspecialchars($method) ?>"
              action="<?= htmlspecialchars($action) ?>"
              class="filter-bar-form">
            <?php foreach ($hiddenFields as $hiddenField): ?>
                <input type="hidden"
                       name="<?= htmlspecialchars($hiddenField['name'] ?? '') ?>"
                       value="<?= htmlspecialchars((string)($hiddenField['value'] ?? '')) ?>">
            <?php endforeach; ?>

            <div class="row g-3 align-items-end">
                <?php foreach ($fields as $field):
                    $type = $field['type'] ?? 'text';
                    if ($type === 'hidden') {
                        continue;
                    }
                    $name = $field['name'] ?? '';
                    $label = $field['label'] ?? '';
                    $value = (string)($field['value'] ?? '');
                    $col = $field['col'] ?? 3;
                    $attrs = $field['attrs'] ?? [];
                    $autoSubmitAttr = !empty($field['auto_submit']) ? ' data-auto-submit="change"' : '';
                    ?>
                    <div class="col-md-<?= htmlspecialchars((string)$col) ?>">
                        <?php if ($label !== ''): ?>
                            <label class="form-label" for="<?= htmlspecialchars($formId . '_' . $name) ?>">
                                <?= htmlspecialchars($label) ?>
                            </label>
                        <?php endif; ?>
                        <?php if ($type === 'select'): ?>
                            <select
                                class="form-select"
                                id="<?= htmlspecialchars($formId . '_' . $name) ?>"
                                name="<?= htmlspecialchars($name) ?>"
                                <?= $autoSubmitAttr ?>
                                <?php foreach ($attrs as $attrKey => $attrValue): ?>
                                    <?= htmlspecialchars($attrKey) ?>="<?= htmlspecialchars($attrValue) ?>"
                                <?php endforeach; ?>
                            >
                                <?php foreach ($field['options'] ?? [] as $option): ?>
                                    <?php
                                    $optionValue = isset($option['value']) ? (string)$option['value'] : '';
                                    $selected = ($optionValue === $value) ? 'selected' : '';
                                    ?>
                                    <option value="<?= htmlspecialchars($optionValue) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($option['label'] ?? $optionValue) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (in_array($type, ['text', 'search', 'date', 'number'], true)): ?>
                            <input
                                type="<?= htmlspecialchars($type) ?>"
                                class="form-control"
                                id="<?= htmlspecialchars($formId . '_' . $name) ?>"
                                name="<?= htmlspecialchars($name) ?>"
                                value="<?= htmlspecialchars($value) ?>"
                                placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                <?= $autoSubmitAttr ?>
                                <?php foreach ($attrs as $attrKey => $attrValue): ?>
                                    <?= htmlspecialchars($attrKey) ?>="<?= htmlspecialchars($attrValue) ?>"
                                <?php endforeach; ?>
                            >
                        <?php elseif ($type === 'custom'): ?>
                            <?= $field['html'] ?? '' ?>
                        <?php endif; ?>
                        <?php if (!empty($field['hint'])): ?>
                            <div class="form-text"><?= htmlspecialchars($field['hint']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($actions)): ?>
                    <div class="col-md-auto d-flex flex-wrap gap-2 align-items-center">
                        <?php foreach ($actions as $actionConfig): ?>
                            <?php
                            $actionType = $actionConfig['type'] ?? 'submit';
                            $variant = 'btn-' . ($actionConfig['variant'] ?? 'primary');
                            $label = $actionConfig['label'] ?? '';
                            $icon = $actionConfig['icon'] ?? null;
                            $col = $actionConfig['col'] ?? null;
                            ?>
                            <?php if ($col): ?>
                                <div class="col-md-<?= htmlspecialchars((string)$col) ?>">
                            <?php endif; ?>
                            <?php if ($actionType === 'submit'): ?>
                                <button type="submit" class="btn <?= htmlspecialchars($variant) ?>">
                                    <?php if ($icon): ?>
                                        <i class="<?= htmlspecialchars($icon) ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($label) ?>
                                </button>
                            <?php elseif ($actionType === 'button'): ?>
                                <button type="button" class="btn <?= htmlspecialchars($variant) ?>" <?= $actionConfig['attrs'] ?? '' ?>>
                                    <?php if ($icon): ?>
                                        <i class="<?= htmlspecialchars($icon) ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($label) ?>
                                </button>
                            <?php elseif ($actionType === 'link'): ?>
                                <a href="<?= htmlspecialchars($actionConfig['url'] ?? '#') ?>"
                                   class="btn <?= htmlspecialchars($variant) ?>">
                                    <?php if ($icon): ?>
                                        <i class="<?= htmlspecialchars($icon) ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($label) ?>
                                </a>
                            <?php elseif ($actionType === 'custom'): ?>
                                <?= $actionConfig['html'] ?? '' ?>
                            <?php endif; ?>
                            <?php if ($col): ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($extra)): ?>
                <div class="mt-3">
                    <?= $extra ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($autoSubmit): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('<?= addslashes($formId) ?>');
    if (!filterForm) {
        return;
    }
    const autoFields = filterForm.querySelectorAll('[data-auto-submit="change"]');
    autoFields.forEach(function (field) {
        field.addEventListener('change', function () {
            filterForm.submit();
        });
    });
});
</script>
<?php endif; ?>
