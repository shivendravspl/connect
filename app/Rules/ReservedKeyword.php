<?php

namespace App\Rules;


use Illuminate\Contracts\Validation\Rule;

class ReservedKeyword implements Rule
{
    protected array $reservedKeywords = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class',
        'clone', 'const', 'continue', 'declare', 'default', 'do', 'echo', 'else',
        'elseif', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile',
        'extends', 'final', 'finally', 'for', 'foreach', 'function', 'global', 'goto',
        'if', 'implements', 'interface', 'instanceof', 'insteadof', 'namespace', 'new',
        'or', 'private', 'protected', 'public', 'return', 'static', 'switch', 'throw',
        'trait', 'try', 'use', 'var', 'while', 'xor', 'yield','!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '[', ']', '{', '}', ';', ':', '"', "'", ',', '<', '>', '.', '/', '?', '\\', '|', '~', '`'

    ];

    public function passes($attribute, $value)
    {
        return !in_array(\Str::lower($value), $this->reservedKeywords);
    }

    public function message()
    {
        return 'The :attribute is a reserved keyword. please try with another name';
    }
}
