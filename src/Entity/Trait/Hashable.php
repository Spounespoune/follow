<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use App\Entity\HashableInterface;
use Doctrine\Persistence\Proxy;

trait Hashable
{
    private const array IGNORED_FIELDS = [
        'id',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'lazyObjectState',
    ];

    public function hash(): string
    {
        $checkString = '';

        $arr = (array) $this;
        ksort($arr);

        foreach ($arr as $k => $v) {
            if ($this->shouldIgnoreField($k)) {
                continue;
            }

            $value = $this->normalizeValue($v);
            $checkString .= sprintf('[%s:%s]', $k, $value);
        }

        return sha1($checkString);
    }

    public function identicalTo(HashableInterface $obj): bool
    {
        return $this->hash() === $obj->hash();
    }

    private function shouldIgnoreField(string $fieldName): bool
    {
        $cleanFieldName = preg_replace('/^.*\0/', '', $fieldName);

        return array_any(self::IGNORED_FIELDS, fn ($ignoredField) => str_contains($cleanFieldName, $ignoredField));
    }

    private function normalizeValue($value): string
    {
        if (null === $value) {
            return 'null';
        }

        if ($value instanceof Proxy) {
            if (!$value->__isInitialized()) {
                $value->__load();
            }

            if ($value instanceof HashableInterface) {
                return $value->hash();
            }
        }

        if ($value instanceof HashableInterface) {
            return $value->hash();
        }

        if (is_object($value) && !method_exists($value, '__toString')) {
            return get_class($value).'@'.spl_object_hash($value);
        }

        return (string) $value;
    }
}
