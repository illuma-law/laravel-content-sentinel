<?php

declare(strict_types=1);

namespace IllumaLaw\ContentSentinel\States\Concerns;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Spatie {@see State::resolveStateObject()} instantiates concrete states with an optional
 * second {@see StateConfig} argument; this trait keeps constructors compatible while delegating to {@see State}.
 */
trait ReceivesOptionalStateConfig
{
    /**
     * @param  Model  $model
     */
    public function __construct($model, protected ?StateConfig $stateConfig = null)
    {
        parent::__construct($model);
    }
}
