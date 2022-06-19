<?php

declare(strict_types=1);

namespace tests\App\Unit\Queue;

use App\Queue\HandlerResolver;
use tests\App\Unit\UnitTestCase;

class HandlerResolverTest extends UnitTestCase
{
    public function testResolve()
    {
        $fakeHandler = new FakeHandler();
        $handlerResolver = new HandlerResolver([$fakeHandler]);
        $resolvedHandler = $handlerResolver->resolve(get_class($fakeHandler));
        $this->assertEquals($fakeHandler, $resolvedHandler);
    }
}
