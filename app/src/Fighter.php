<?php
namespace Tournament;

use Tournament\Inventory\Armor;
use Tournament\Inventory\Axe;
use Tournament\Inventory\Buckler;
use Tournament\Inventory\Weapon;

abstract class Fighter
{
    protected int $totalHP;
    protected int $currentHP;
    protected Weapon $weapon;

    private ?string $speciality;
    private ?Buckler $buckler = null;
    private ?Armor $armor = null;

    private int $blows = 0;

    /** an armor : reduce all received damages by 3 & reduce delivered damages by one **/
    const REDUCE_ALL_RECEIVED_DMG = 3;
    const REDUCE_DELIVERED_DMG = 1;

    /**
     *  a vicious Swordsman is a Swordsman that put poison on his weapon.
     * poison add 20 damages on two first blows
     */
    const VICIOUS_SWORDSMAN_POISON_DMG = 20;
    const VICIOUS_SWORDSMAN_POISON_BLOW_COUNT = 2;

    /**
     * a veteran Highlander goes Berserk once his hit points are under 30% of his initial total
     * once Berserk, he doubles his damages
     */
    const VETERAN_HIGHLANDER_ACTIVATES_BERSERK = 0.3; // totalHP * 0.3
    const VETERAN_HIGHLANDER_BERSERK_MULTIPLIER = 2;


    public function __construct(string $speciality = null)
    {
        $this->speciality = $speciality;
    }

    // engage
    public function engage(Fighter $fighter)
    {
        $attacker = $fighter;
        $defender = $this;

        while (true)
        {
            if ($fighter->hitPoints() <= 0 || $this->hitPoints() <= 0) break;

            $attacker = $attacker === $fighter ? $this : $fighter;
            $defender = $defender === $this ? $fighter : $this;

            $attacker->madeBlow();

            if ($attacker->getBlows() % 2 === 0 &&
                !is_null($defender->buckler) &&
                $defender->buckler->getBlocks() > 0)
            {
                if ($attacker->weapon instanceof Axe) $defender->buckler->blocked();
            } else {
                $attacker->blow($defender);
            }
        }
    }

    public function equip(string $item): Fighter
    {
        switch ($item)
        {
            case "buckler":
                $this->buckler = new Buckler($item, 3);
                break;
            case "armor":
                $this->armor = new Armor($item);
                break;
            case "axe":
                $this->weapon = new Axe($item, 6);
                break;
            // Can add more cases e.g. case "sword" -> creates new Sword etc.
        }
        return $this;
    }

    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    public function getTotalHP(): int
    {
        return $this->totalHP;
    }

    public function hitPoints(): int
    {
        if ($this->currentHP < 0) $this->currentHP = 0;
        return $this->currentHP;
    }

    public function getWeapon(): Weapon
    {
        return $this->weapon;
    }

    public function blow(Fighter $fighter): void
    {
        $fighter->receivedBlow($this->weapon->weaponDmg());
    }

    public function receivedBlow(int $dmg): void
    {
        if (!is_null($this->armor))
        {
            $this->currentHP -= $dmg - self::REDUCE_ALL_RECEIVED_DMG;
        } else {
            $this->currentHP -= $dmg;
        }
    }

    public function madeBlow(): void
    {
        $this->blows++;
    }

    public function getBlows(): int
    {
        return $this->blows;
    }
}