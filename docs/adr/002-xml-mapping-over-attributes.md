# ADR 002: Doctrine XML Mapping Over PHP Attributes

## Status
Accepted

## Context
Doctrine supports multiple mapping formats: PHP attributes (#[ORM\Entity]), XML, and YAML. Using PHP attributes on domain entities would introduce a dependency on Doctrine ORM in the domain layer.

## Decision
Use XML mapping files stored in `Infrastructure/Persistence/Mapping/`. Domain entities remain plain PHP classes with no Doctrine imports.

## Consequences
- Domain layer stays clean — no `use Doctrine\ORM\Mapping as ORM`
- Mapping is maintained separately, which adds indirection
- IDE auto-completion for mapping is weaker than with attributes
- File naming convention: `Entity.orm.xml`
