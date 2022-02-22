<?php

namespace Transmission\Model;

/**
 * @author Ramon Kleiss <ramon@cubilon.nl>
 */
class File extends AbstractModel
{
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var integer
     */
    protected $size;

    /**
     * @var integer
     */
    protected $completed;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param integer $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return integer
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param integer $size
     */
    public function setCompleted(int $completed): void
    {
        $this->completed = $completed;
    }

    /**
     * @return integer
     */
    public function getCompleted(): int
    {
        return $this->completed;
    }

    /**
     * @return boolean
     */
    public function isDone(): bool
    {
        return $this->getSize() == $this->getCompleted();
    }

    /**
     * {@inheritDoc}
     */
    public static function getMapping(): array
    {
        return [
            'name'           => 'name',
            'length'         => 'size',
            'bytesCompleted' => 'completed'
        ];
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
