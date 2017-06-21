<?php

namespace Commercetools\Generator;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class NamespaceChangeVisitor extends NodeVisitorAbstract
{
    private $old;
    private $new;
    private $uses = [];
    /**
     * @var Node\Name
     */
    private $namespace;

    public function __construct($oldNameSpace, $newNameSpace)
    {
        $this->old = $oldNameSpace;
        $this->new = $newNameSpace;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->uses = [];
            $this->namespace = $node->name;
        }
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $this->uses[$use->name->toString()] = ['name' => $use->name, 'alias' => $use->alias];
            }
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $node->name = new Node\Name(str_replace($this->old, $this->new, $node->name->toString()));
            return $node;
        }
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as &$use) {
                $use->name = new Node\Name(
                    str_replace($this->old, $this->new, $use->name->toString())
                );
            }
            return $node;
        }
        if ($node instanceof Node\Name) {
            $name = $node->toString();
            if (isset($this->uses[$name]) && $this->uses[$name]['name'] != $node) {
                return new Node\Name($this->uses[$name]['alias']);
            }
            if ($node !== $this->namespace &&
                !isset($this->uses[$name]) &&
                strpos($name, $this->namespace->toString()) === 0
            ) {
                $node = new Node\Name(
                    trim(str_replace($this->namespace->toString(), '', $node->toString()), '\\')
                );
                return $node;
            }
            return $node;
        }
        return null;
    }
}
