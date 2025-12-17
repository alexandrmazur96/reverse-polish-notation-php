# RPN - Reverse Polish Notation Calculator

A PHP library for parsing and evaluating mathematical expressions using **Reverse Polish Notation (RPN)** with support for infix notation conversion via the **Shunting Yard algorithm**.

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

- ✅ **Parse infix mathematical expressions** to RPN using the Shunting Yard algorithm
- ✅ **Evaluate RPN expressions** with correct operator precedence
- ✅ **Support for standard operators:**
  - Binary operators: `+`, `-`, `*` (×), `/` (÷)
  - Power operator: `^`
  - Factorial: `!`
- ✅ **Support for mathematical functions:**
  - `sqrt()` - Square root
  - `pow()` - Power function
  - `log()` - Natural logarithm
  - `exp()` - Exponential function
  - `∛` (cube root)
  - `∜` (fourth root)
- ✅ **Proper operator associativity** handling
- ✅ **Type-safe PHP 8.3+** with strict types
- ✅ **Fully tested** with PHPUnit
- ✅ **Static analysis** with Psalm
- ✅ **Code quality** enforced by PHP CodeSniffer

## Usage

### Direct RPN Expression Evaluation

```php
use Rpn\Expression;
use Rpn\Operands\Number;
use Rpn\Operators\{Addition, Multiplication};

// Evaluate: 3 + 4 * 2 = 11 (but in RPN: 3 4 2 * +)
$expression = new Expression(
    new Number(3),
    new Number(4),
    new Number(2),
    new Multiplication(),
    new Addition(),
);

echo $expression->evaluate(); // Output: 11
```

### Parsing Infix Expressions to RPN

```php
use Rpn\Parsers\MathematicalStringParser;
use Rpn\Expression;

// Parse an infix expression
$parser = new MathematicalStringParser('3 + 4 * 2');
$tokens = $parser->parse();

// Convert to Expression and evaluate
$expression = new Expression(...$tokens);
echo $expression->evaluate(); // Output: 11
```

### Using Mathematical Functions

```php
use Rpn\Parsers\MathematicalStringParser;
use Rpn\Expression;

// Parse expressions with functions
$parser = new MathematicalStringParser('sqrt(16) + pow(2, 3)');
$expression = new Expression(...$parser->parse());

echo $expression->evaluate(); // Output: 12 (4 + 8)
```

### Supported Syntax

The parser supports both standard ASCII and Unicode mathematical symbols:

| Operation | ASCII | Unicode | Example |
|-----------|-------|---------|---------|
| Multiply | `*` | `×` | `3 × 4` |
| Divide | `/` | `÷` | `10 ÷ 2` |
| Power | `^` | - | `2 ^ 3` |
| Factorial | `!` | - | `5!` |
| Square Root | `sqrt()` | `√` | `sqrt(16)` or `√16` |
| Cube Root | - | `∛` | `∛27` |
| Fourth Root | - | `∜` | `∜81` |

## Architecture

### Core Components

#### `Expression` Class
Evaluates an RPN expression using a stack-based algorithm:
1. Push operands onto the stack
2. When an operator is encountered, pop operands, apply the operator, and push the result back
3. The final stack value is the result

#### `MathematicalStringParser` Class
Converts infix notation to RPN using the **Shunting Yard algorithm**:
1. Tokenizes the input string
2. Handles operator precedence
3. Manages parentheses
4. Respects operator associativity (right-associative for power and functions)
5. Handles unary operators (factorial, negation)

#### Operator Classes
- Binary operators: `Addition`, `Subtraction`, `Multiplication`, `Division`, `Power`
- Unary operators: `Sqrt`, `Log`, `Exp`, `CubeRoot`, `FourthRoot`, `Factorial`

#### Operand Classes
- `Number` - Represents numeric values

### Operator Precedence

From highest to lowest:
1. **Factorial** (`!`) - precedence 5
2. **Unary functions** (sqrt, log, exp, ∛, ∜) - precedence 4
3. **Power** (`^`) - precedence 3 (right-associative)
4. **Multiplication/Division** (`*`, `/`) - precedence 2
5. **Addition/Subtraction** (`+`, `-`) - precedence 1

## Development

### Running Tests

```bash
composer test
# or
./vendor/bin/phpunit
```

### Code Quality Checks

```bash
# Static analysis
./vendor/bin/psalm

# Code style
./vendor/bin/phpcs

# Parallel linting
./vendor/bin/parallel-lint src tests
```

## Examples

```php
use Rpn\Parsers\MathematicalStringParser;
use Rpn\Expression;

// Example 1: Basic arithmetic
$expr = new MathematicalStringParser('(3 + 4) * 2');
echo new Expression(...$expr->parse())->evaluate(); // 14

// Example 2: Complex expression with functions
$expr = new MathematicalStringParser('sqrt(16) + log(2.718281828)');
echo new Expression(...$expr->parse())->evaluate(); // ~5

// Example 3: Power operations
$expr = new MathematicalStringParser('2 ^ 3 ^ 2'); // Right-associative: 2^(3^2) = 2^9 = 512
echo new Expression(...$expr->parse())->evaluate(); // 512

// Example 4: Factorial
$expr = new MathematicalStringParser('5!');
echo new Expression(...$expr->parse())->evaluate(); // 120

// Example 5: Unary minus (negation)
$expr = new MathematicalStringParser('-5 + 3');
echo new Expression(...$expr->parse())->evaluate(); // -2
```

## How It Works

### Infix to RPN Conversion (Shunting Yard Algorithm)

Example: `3 + 4 * 2`

| Token | Action | Output | Operator Stack |
|-------|--------|--------|-----------------|
| `3` | Push to output | `3` | |
| `+` | Push operator | `3` | `+` |
| `4` | Push to output | `3 4` | `+` |
| `*` | Higher precedence, push | `3 4` | `+ *` |
| `2` | Push to output | `3 4 2` | `+ *` |
| End | Pop all operators | `3 4 2 * +` | |

### RPN Evaluation

Expression: `3 4 2 * +`

| Token | Stack After | Explanation |
|-------|-------------|-------------|
| `3` | `[3]` | Push 3 |
| `4` | `[3, 4]` | Push 4 |
| `2` | `[3, 4, 2]` | Push 2 |
| `*` | `[3, 8]` | Pop 4, 2; compute 4*2=8; push 8 |
| `+` | `[11]` | Pop 3, 8; compute 3+8=11; push 11 |

## Error Handling

The library throws specific exceptions for error cases:

- `UnknownFunctionException` - Thrown when an unknown function name is encountered
- `UnknownTokenException` - Thrown when an invalid token is encountered

Both exceptions extend `InvalidArgumentException`, so you can catch them individually or as a group.

Example:
```php
use Rpn\Parsers\MathematicalStringParser;
use Rpn\Expression;
use Rpn\Exceptions\UnknownFunctionException;
use Rpn\Exceptions\UnknownTokenException;

try {
    $parser = new MathematicalStringParser('unknown_func()');
    new Expression(...$parser->parse());
} catch (UnknownFunctionException $e) {
    echo $e->getMessage(); // "Unknown function: unknown_func"
}

try {
    $parser = new MathematicalStringParser('5 @@ 3');
    new Expression(...$parser->parse());
} catch (UnknownTokenException $e) {
    echo $e->getMessage(); // "Unknown token: @@"
}
```

## License

This library is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

Olexandr Mazur <alexandrmazur96@gmail.com>

:star: Star us on GitHub — it motivates us a lot! 😀
