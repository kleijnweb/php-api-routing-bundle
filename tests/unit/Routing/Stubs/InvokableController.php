<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\PhpApi\RoutingBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\PhpApi\RoutingBundle\Tests\Routing\Stubs;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class InvokableController
{
    public function __invoke(): Response
    {
        return new Response();
    }
}
