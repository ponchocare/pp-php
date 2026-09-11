<?php

namespace PonchoPay\Utils;

/**
 * Creates a new Binary object, representing a specific number of bytes.
 */
final class Binary
{
    private int $bytes;

    /**
     * This constructor is intended for internal use to set the underlying byte value.
     *
     * @param int $bytes the number of bytes this instance represents
     */
    private function __construct(int $bytes)
    {
        $this->bytes = $bytes;
    }

    /**
     * Retrieves the raw byte count stored within the Binary object.
     *
     * @return int the value of the stored bytes
     */
    public function toBytes(): int
    {
        return $this->bytes;
    }

    /**
     * Factory method to create a Binary object directly from a raw byte integer value.
     *
     * @param int $bytes the raw byte count to wrap in a Binary object
     *
     * @return Binary the new Binary instance
     */
    public static function fromBytes(int $bytes): self
    {
        return new Binary($bytes);
    }

    /**
     * Factory method to create a Binary object representing a size given in Kibibytes (KiB).
     *
     * @param int $kibibytes the size in Kibibytes
     *
     * @return Binary the new Binary instance, scaled to bytes
     */
    public static function fromKibibytes(int $kibibytes): self
    {
        return Binary::fromBytes($kibibytes * 1024);
    }

    /**
     * Factory method to create a Binary object representing a size given in Mebibytes (MiB).
     *
     * @param int $mebibytes the size in Mebibytes
     *
     * @return Binary the new Binary instance, scaled to bytes
     */
    public static function fromMebibytes(int $mebibytes): self
    {
        return Binary::fromKibibytes($mebibytes * 1024);
    }
}
