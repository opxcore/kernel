<?php
/*
 * This file is part of the OpxCore.
 *
 * Copyright (c) Lozovoy Vyacheslav <opxcore@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpxCore\Kernel;

use OpxCore\App\Interfaces\AppInterface;
use OpxCore\Kernel\Interfaces\KernelInterface;
use OpxCore\Pipeline\Exceptions\PipelineException;
use OpxCore\Pipeline\Pipeline;
use OpxCore\Request\Interfaces\RequestInterface;
use OpxCore\Response\Interfaces\ResponseInterface;

class Kernel implements KernelInterface
{
    /**
     * @var AppInterface Application instance this kernel used by.
     */
    protected AppInterface $app;

    /**
     * @var array Global middlewares.
     */
    protected array $middlewares;

    /**
     * Kernel constructor.
     *
     * @param AppInterface $app
     * @param array $middlewares
     */
    public function __construct(AppInterface $app, array $middlewares = [])
    {
        $this->app = $app;
        $this->middlewares = $middlewares;
    }

    /**
     * Handle request and transform it to response.
     *
     * @param RequestInterface $request
     *
     * @return  ResponseInterface
     * @throws PipelineException
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        // 1. send request through global middlewares
        // 2. match route
        // 3. send request through route middlewares
        // 4. send request through controller middlewares
        // 5. run corresponding controller or command
        // 6. get response

        $this->app->profiler()->start('kernel.handle');

        $pipeline = new Pipeline($this->app->container());

        $response = $pipeline
            ->send($request)
            ->through($this->middlewares)
            ->via('handle')
            ->then([$this, 'processRouter'])
            ->run();

        $this->app->profiler()->stop('kernel.handle');

        return $response;
    }

    protected function processRouter(RequestInterface $request): ResponseInterface
    {

    }
}