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
    protected ?RuleInterface $requiredRule = null;

    public function __construct(
        private readonly Validator $validator,
        private readonly string $type,
        private readonly array $supportedTypes
    ) {
    }

    protected function isTypeSupported(string $type): bool
    {
        return in_array($type, $this->supportedTypes);
    }

    protected function addRule(RuleInterface $rule): void
    {
        $name = $rule->getName();
        if ($name === null || $name === '') {
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
        if ($this->requiredRule !== null) {
            if (!$this->requiredRule->isSatisfied($verifiable)) {
                return false;
            }
        } elseif ($verifiable === null) {
            return true;
        }

        if (!$this->isTypeSupported(gettype($verifiable))) {
            throw new UnsupportedTypeException();
        }

        return $this->validator->validate($this, $verifiable);
    }

    public function required(): AbstractSchema
    {
        $this->requiredRule = new RequiredRule();
        return $this;
    }

    public function test(string $ruleName, mixed ...$parameters): AbstractSchema
    {
        $ruleFunc = $this->validator->getValidator($this->type, $ruleName);
        $this->addRule(new CustomRule($ruleFunc, $parameters));
        return $this;
    }
}
