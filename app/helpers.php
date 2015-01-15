<?php
/**
 * Global Helpers
 */
/**
 * Renders a breadcrumb trail
 *
 * @param array $trail
 * @param bool  $buttons
 *
 * @return string
 */
function renderBreadcrumbs( $trail = array(), $buttons = false )
{
    $_html = null;

    foreach ( $trail as $_name => $_href )
    {
        $_class = false === $_href ? ' class="active" ' : null;
        $_href = false !== $_href ? '<a href="' . $_href . '">' . $_name . '</a>' : $_name;

        $_html .= '
<li ' . $_class . '>' . $_href . '</li>';
    }

    $_spinner = <<<HTML
<span class="breadcrumb-loader pull-right" style="display: none;"><img src="/img/bc-loading.gif" alt="" /></span>
HTML;

    $_buttons =
        false === $buttons
            ? null
            : <<<HTML
<div class="bc-controls">
    <button type="button" id="resource-new" class="btn btn-sm btn-primary">New</button>
    <button type="button" id="resource-save" disabled="disabled" class="btn btn-sm btn-warning">Save</button>
    <button type="button" id="resource-delete" disabled="disabled" class="btn btn-sm btn-danger">Delete</button>
</div>
HTML;

    return <<<HTML
<div id="breadcrumb" class="col-lg-12">
    <a href="#" class="show-sidebar pull-left"><i class="fa fa-bars"></i></a>
    <ol class="breadcrumb pull-left">{$_html}</ol>
    {$_buttons}{$_spinner}
</div>
HTML;
}
