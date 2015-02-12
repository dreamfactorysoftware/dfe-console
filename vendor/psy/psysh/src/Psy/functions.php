<?php

namespace Psy;

<<<<<<< HEAD
if (!function_exists('Psy\sh')) {
    /**
     * Command to return the eval-able code to startup PsySH.
     *
     *     eval(\Psy\sh());
     *
     * @return string
     */
    function sh()
    {
        return 'extract(\Psy\Shell::debug(get_defined_vars(), $this ?: null));';
    }
=======
/**
 * Command to return the eval-able code to startup PsySH.
 *
 *     eval(\Psy\sh());
 *
 * @return string
 */
function sh()
{
    return 'extract(\Psy\Shell::debug(get_defined_vars(), $this ?: null));';
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
}
