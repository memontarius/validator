<?php

namespace Hexlet\Validator;

use Hexlet\Validator\Exception\UnsupportedTypeException;
use Hexlet\Validator\Rules\Shared\CustomRule;
use Hexlet\Validator\Rules\Shared\RequiredRule;

abstract class AbstractSchema
{
    /**
     * @var RuleInterface[]
     */
    private array $rules = [];
    private readonly Validator $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    abstract public static function getName(): string;

    protected function isSupportedType(string $type): bool
    {
        return true;
    }

    protected function addRule(RuleInterface $rule): void
    {
        $name = $rule->getName();
        if (empty($name)) {
            $this->rules[] = $rule;
        } else {
            $this->rules[$name] = $rule;
        }
    }

    /**
     * @return RuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function isValid(mixed $verifiable): bool
    {
        if ($verifiable !== null && !$this->isSupportedType(gettype($verifiable))) {
            throw new UnsupportedTypeException();
        }
        return $this->validator->validate($this, $verifiable);
    }

    public function required(): AbstractSchema
    {
        $this->addRule(new RequiredRule());
        return $this;
    }

    public function test(string $ruleName, ...$parameters): AbstractSchema
    {
        $ruleFunc = $this->validator->getValidator(static::getName(), $ruleName);
        $this->addRule(new CustomRule($ruleFunc, $parameters));
        return $this;
    }
}
