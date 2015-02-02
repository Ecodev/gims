<?php

namespace Application\Service;

/**
 * Exception to be thrown if a permission was denied (typically when using REST API)
 */
class PermissionDeniedException extends \Exception
{
}
