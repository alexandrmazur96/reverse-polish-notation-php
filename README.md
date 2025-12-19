# RPN - Reverse Polish Notation Calculator

A flexible and extensible PHP library for parsing and evaluating mathematical expressions using **Reverse Polish Notation (RPN)**. It uses a **Shunting-yard algorithm** implementation that is configurable and easy to use.

## What is Reverse Polish Notation?

Reverse Polish Notation (also called postfix notation) is a mathematical notation in which operators follow their operands. For example:
- **Infix:** `3 + 4 * 2`
- **RPN/Postfix:** `3 4 2 * +`

RPN eliminates the need for parentheses and operator precedence rules during evaluation, making it simpler and faster to compute.

## Installation

```bash
composer require php-rpn/rpn
```

### Requirements
- PHP 8.3 or higher

## Features

- ✅ **Fluent Builder Interface** for creating customized parsers.
- ✅ **Parse infix mathematical expressions** to an RPN stream using the Shunting-yard algorithm.
- ✅ **Extensible Operator Registry** to add or override operators.
- ✅ **Evaluate RPN expression streams**.
- ✅ **Support for standard operators:**
  - Binary operators: `+`, `-`, `*` (×), `/` (÷)
  - Unary operators: `-` (negation), `!` (factorial)
- ✅ **Support for mathematical functions:**
  - `sqrt()`|`√` - Square root
  - `pow()`|`^` - Power function
  - `log()` - Natural logarithm
  - `exp()` - Exponential function
  - `min()` - Minimum of two values
  - `max()` - Maximum of two values
  - `∛` (cube root)
  - `∜` (fourth root)
- ✅ **Variable support** for dynamic expressions.
- ✅ **Proper operator associativity** handling.
- ✅ **Type-safe PHP 8.3+** with strict types.

## Usage

The library is designed with a fluent builder to make parsing and evaluation straightforward.

### Basic Usage

```php
use Rpn\Expression;
use Rpn\Parsers\ShuntingYardParserBuilder;

// 1. Get a pre-configured parser for standard math operations.
$parser = ShuntingYardParserBuilder::math()->build();

// 2. Parse an infix expression into an RPN stream.
$rpnStream = $parser->parse('3 + 4 * 2');

// 3. Evaluate the stream.
$evaluator = new Expression();
echo $evaluator->evaluate($rpnStream)->value(); // Output: 11
```

### Using Mathematical Functions

```php
use Rpn\Expression;
use Rpn\Parsers\ShuntingYardParserBuilder;

$parser = ShuntingYardParserBuilder::math()->build();
$evaluator = new Expression();

// Parse expressions with functions
$rpnStream = $parser->parse('sqrt(16) + pow(2, 3)');
echo $evaluator->evaluate($rpnStream)->value(); // Output: 12 (4 + 8)
```

### Using Variables

```php
use Rpn\Expression;
use Rpn\Parsers\ShuntingYardParserBuilder;

$parser = ShuntingYardParserBuilder::math()->build();
$evaluator = new Expression();

// Variables should start with a colon (:)

// Parse an expression with variables
$rpnStream = $parser->parse('(:a * :b + :c) * 2');

$evaluator->evaluate($rpnStream, [':a' => 3, ':b' => 4, ':c' => 5])->value(); // Output: 34
```

### Customization

You can easily add your own custom operators if you need to. Just implement the `OperatorInterface` and register it with the parser builder:

```php
use Rpn\Expression;
use Rpn\Parsers\ShuntingYardParserBuilder;
use Rpn\Operators\OperatorInterface;
use Rpn\Operands\OperandInterface;
use Rpn\Enum\Associativity;
use Rpn\Enum\OperatorType;

// Define a custom "double factorial" operator
class DoubleFactorial implements OperatorInterface {
    public function getPrecedence(): int { return 10; }
    public function getAssociativity(): Associativity { return Associativity::Left; }
    public function getType(): OperatorType { return OperatorType::UnaryPostfix; }
    public function apply(OperandInterface ...$operands): OperandInterface {
        $val = $operands[0]->value();
        $result = 1;
        for ($i = $val; $i >= 1; $i -= 2) {
            $result *= $i;
        }
        return new \Rpn\Operands\Number($result);
    }
}

// Create a parser and add the new operator
$parser = ShuntingYardParserBuilder::math()
    ->addOperator('!!', new DoubleFactorial())
    ->build();

$evaluator = new Expression();

$rpnStream = $parser->parse('5!!'); // 5 * 3 * 1
echo $evaluator->evaluate($rpnStream)->value(); // Output: 15
```

### Supported Syntax

The default parser supports both standard ASCII and Unicode mathematical symbols:

| Operation            | ASCII       | Unicode | Example               |
|----------------------|-------------|---------|-----------------------|
| Addition             | `+`         | -       | `1 + 7`               |
| Subtraction          | `-`         | -       | `5 - 49`              |
| Multiply             | `*`         | `×`     | `3 × 4`               |
| Divide               | `/`         | `÷`     | `10 ÷ 2`              |
| Power                | `^`, `pow`  | -       | `2 ^ 3`               |
| Factorial            | `!`         | -       | `5!`                  |
| Square Root          | `sqrt`      | `√`     | `sqrt(16)` or `√16`   |
| Cube Root            | -           | `∛`     | `∛27`                 |
| Fourth Root          | -           | `∜`     | `∜81`                 |
| Exponential function | `exp`       | -       | `exp(3)`              |
| Min                  | `min`       | -       | `min(3, :x)`          |
| Max                  | `min`       | -       | `max(7, :x)`          |
| Log                  | `log`       | -       | `log(10)`             |
| Percent              | `%`         | -       | `5%`                  |
| Negation             | `-`         | -       | `-3`                  |

## Development

Feel free to contribute! Fork the repository and submit a pull request.
Just make sure everything satisfies the coding standards and all tests pass.

```bash
composer checks
```

## How It Works

### 1. Parsing (Infix to RPN)

The `ShuntingYardParser` converts an infix string like `3 + 4 * 2` into an `ExpressionPartsStream`.

| Token | Action | Output Stream (Conceptual) | Operator Stack |
|-------|--------|----------------------------|----------------|
| `3` | Add to stream | `[Number(3)]` | |
| `+` | Push to stack | `[Number(3)]` | `[+]` |
| `4` | Add to stream | `[Number(3), Number(4)]` | `[+]` |
| `*` | Higher precedence, push | `[Number(3), Number(4)]` | `[+, *]` |
| `2` | Add to stream | `[Number(3), Number(4), Number(2)]` | `[+, *]` |
| End | Pop all operators | `[Number(3), Number(4), Number(2), *, +]` | |

### 2. Evaluation

The `Expression::evaluate()` method iterates the `ExpressionPartsStream` and uses a stack to compute the result.

Expression Stream: `[Number(3), Number(4), Number(2), *, +]`

| Token | Stack After | Explanation |
|-------|-------------|-------------|
| `3` | `[3]` | Push 3 |
| `4` | `[3, 4]` | Push 4 |
| `2` | `[3, 4, 2]` | Push 2 |
| `*` | `[3, 8]` | Pop 4, 2; compute 4*2=8; push 8 |
| `+` | `[11]` | Pop 3, 8; compute 3+8=11; push 11 |

## License

This library is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

Olexandr Mazur <alexandrmazur96@gmail.com>

:star: Star this project on GitHub — it motivates me a lot!
