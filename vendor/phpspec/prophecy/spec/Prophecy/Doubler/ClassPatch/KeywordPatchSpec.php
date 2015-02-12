<?php

namespace spec\Prophecy\Doubler\ClassPatch;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Doubler\Generator\Node\MethodNode;

class KeywordPatchSpec extends ObjectBehavior
{
    function it_is_a_patch()
    {
        $this->shouldBeAnInstanceOf('Prophecy\Doubler\ClassPatch\ClassPatchInterface');
    }

<<<<<<< HEAD
    function its_priority_is_49()
    {
        $this->getPriority()->shouldReturn(49);
=======
    function its_priority_is_50()
    {
        $this->getPriority()->shouldReturn(50);
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    }

    /**
     * @param \Prophecy\Doubler\Generator\Node\ClassNode $node
     * @param \Prophecy\Doubler\Generator\Node\MethodNode $method1
     * @param \Prophecy\Doubler\Generator\Node\MethodNode $method2
     * @param \Prophecy\Doubler\Generator\Node\MethodNode $method3
     */
    function it_will_remove_echo_and_eval_methods($node, $method1, $method2, $method3)
    {
        $node->removeMethod('eval')->shouldBeCalled();
        $node->removeMethod('echo')->shouldBeCalled();

        $method1->getName()->willReturn('echo');
        $method2->getName()->willReturn('eval');
        $method3->getName()->willReturn('notKeyword');

        $node->getMethods()->willReturn(array(
            'echo' => $method1,
            'eval' => $method2,
            'notKeyword' => $method3,
        ));

        $this->apply($node);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
