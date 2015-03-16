<?php
namespace Ratchet\RFC6455\Messaging\Protocol;

class Message implements MessageInterface {
    /**
     * @var \SplDoublyLinkedList
     */
    private $_frames;

    /** @var bool  */
    private $binary = false;

    public function __construct() {
        $this->_frames = new \SplDoublyLinkedList;
    }

    /**
     * {@inheritdoc}
     */
    public function count() {
        return count($this->_frames);
    }

    public function offsetExists($index) {
        return $this->_frames->offsetExists($index);
    }

    public function offsetGet($index) {
        return $this->_frames->offsetGet($index);
    }

    public function offsetSet($index, $newval) {
        throw new \DomainException('Frame access in messages is read-only');
    }

    public function offsetUnset($index) {
        throw new \DomainException('Frame access in messages is read-only');
    }

    /**
     * {@inheritdoc}
     */
    public function isCoalesced() {
        if (count($this->_frames) == 0) {
            return false;
        }

        $last = $this->_frames->top();

        return ($last->isCoalesced() && $last->isFinal());
    }

    /**
     * {@inheritdoc}
     * @todo Also, I should perhaps check the type...control frames (ping/pong/close) are not to be considered part of a message
     * @todo What should we do if there are binary and text mixed together?
     */
    public function addFrame(FrameInterface $fragment) {
        if ($this->_frames->isEmpty()) {
            $this->binary = $fragment->getOpcode() == Frame::OP_BINARY;
        }
        $this->_frames->push($fragment);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpcode() {
        if (count($this->_frames) == 0) {
            throw new \UnderflowException('No frames have been added to this message');
        }

        return $this->_frames->bottom()->getOpcode();
    }

    /**
     * {@inheritdoc}
     */
    public function getPayloadLength() {
        $len = 0;

        foreach ($this->_frames as $frame) {
            try {
                $len += $frame->getPayloadLength();
            } catch (\UnderflowException $e) {
                // Not an error, want the current amount buffered
            }
        }

        return $len;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException('Message has not been put back together yet');
        }

        $buffer = '';

        foreach ($this->_frames as $frame) {
            $buffer .= $frame->getPayload();
        }

        return $buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents() {
        if (!$this->isCoalesced()) {
            throw new \UnderflowException("Message has not been put back together yet");
        }

        $buffer = '';

        foreach ($this->_frames as $frame) {
            $buffer .= $frame->getContents();
        }

        return $buffer;
    }

    /**
     * @return boolean
     */
    public function isBinary()
    {
        return $this->binary;
    }
}