<?php declare(strict_types=1);

namespace mglaman\PHPStanDrupal\Drupal;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use mglaman\PHPStanDrupal\Type\EntityStorage\ConfigEntityStorageType;
use mglaman\PHPStanDrupal\Type\EntityStorage\ContentEntityStorageType;
use mglaman\PHPStanDrupal\Type\EntityStorage\EntityStorageType;
use PHPStan\Type\ObjectType;

final class EntityData
{

    /**
     * @var string
     */
    private $entityTypeId;

    /**
     * @var string|null
     */
    private $className;

    /**
     * @var string|null
     */
    private $storageClassName;

    public function __construct(string $entityTypeId, array $definition)
    {
        $this->entityTypeId = $entityTypeId;
        $this->className = $definition['class'] ?? null;
        $this->storageClassName = $definition['storage'] ?? null;
    }

    public function getClassType(): ?ObjectType
    {
        return $this->className === null ? null : new ObjectType($this->className);
    }

    public function getStorageType(): ?ObjectType
    {
        if ($this->storageClassName === null) {
            // @todo get entity type class reflection and return proper storage for entity type
            // example: config storage, sqlcontententitystorage, etc.
            // $className = reflectedDecision.
            return null;
        }

        $storageType = new ObjectType($this->storageClassName);
        if ((new ObjectType(EntityStorageInterface::class))->isSuperTypeOf($storageType)->no()) {
            return null;
        }
        if ((new ObjectType(ConfigEntityStorageInterface::class))->isSuperTypeOf($storageType)->yes()) {
            return new ConfigEntityStorageType($this->entityTypeId, $this->storageClassName);
        }
        if ((new ObjectType(ContentEntityStorageInterface::class))->isSuperTypeOf($storageType)->yes()) {
            return new ContentEntityStorageType($this->entityTypeId, $this->storageClassName);
        }
        return new EntityStorageType($this->entityTypeId, $this->storageClassName);
    }
}
