<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\DbConnection\Aspect;

use Hyperf\DbConnection\Annotation\Transactional;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
class TransactionAspect extends AbstractAspect
{
    public $annotations = [
        Transactional::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $c = $this->getAnnotationConnection($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
        $connection = $c->connection;

        return Db::connection($connection)->transaction(
            function () use ($proceedingJoinPoint) {
                return $proceedingJoinPoint->process();
            }
        );
    }

    protected function getAnnotationConnection(string $className, string $method): ?Transactional
    {
        $collector = AnnotationCollector::get("${className}._m.${method}");
        $res = $collector[Transactional::class] ?? null;
        if($res instanceof Transactional){
            return $res;
        }
        return NULL;
    }

}
