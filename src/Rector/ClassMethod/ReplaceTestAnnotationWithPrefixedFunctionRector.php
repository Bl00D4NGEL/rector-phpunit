<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector\ReplaceTestAnnotationWithPrefixedFunctionRectorTest
 */
final class ReplaceTestAnnotationWithPrefixedFunctionRector extends AbstractRector
{
    public function __construct(
        private readonly TestsNodeAnalyzer $testsNodeAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace @test with prefixed function', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function onePlusOneShouldBeTwo()
    {
        $this->assertSame(2, 1+1);
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function testOnePlusOneShouldBeTwo()
    {
        $this->assertSame(2, 1+1);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        if ($this->isName($node->name, 'test*')) {
            return null;
        }

        foreach ($node->getComments() as $comment) {
            if (str_contains($comment->getText(), '@test')) {
                $node->name->name = 'test' . ucfirst($node->name->name);

                return $node;
            }
        }

        return null;
    }
}