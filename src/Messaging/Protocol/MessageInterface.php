<?php
namespace Ratchet\RFC6455\Messaging\Protocol;

interface MessageInterface extends DataInterface, \ArrayAccess, \Countable {
    /**
     * @param FrameInterface $fragment
     * @return MessageInterface
     */
    function addFrame(FrameInterface $fragment);

    /**
     * @return int
     */
    function getOpcode();
}