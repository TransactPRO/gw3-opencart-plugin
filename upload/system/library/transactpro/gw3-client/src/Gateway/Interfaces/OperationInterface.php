<?php declare(strict_types = 1);

/*
 * This file is part of the transact-pro/gw3-client package.
 *
 * (c) Transact Pro
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TransactPro\Gateway\Interfaces;

use TransactPro\Gateway\Exceptions\ValidatorException;
use TransactPro\Gateway\Http\Request;

/**
 * Interface OperationInterface.
 *
 * All defined operations should implement this interface.
 */
interface OperationInterface
{
    /**
     * Build build Request object
     *
     * @throws ValidatorException
     * @return Request
     */
    public function build();
}
