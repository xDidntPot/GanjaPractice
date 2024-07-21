<?php

/*
 *
 * Developed by Warro#7777
 * Join Ganja: ganja.bet:19132
 * My Discord: https://discord.gg/vasar
 * Repository: https://github.com/Wqrro/Ganja
 *
 */

declare(strict_types=1);

namespace Warro\entities;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
use Warro\Base;
use Warro\User;

class VasarPearl extends EnderPearl{

	protected float $gravity = 0.065;
	protected float $drag = 0.0085;

	public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null){
		parent::__construct($location, $shootingEntity, $nbt);
		$this->setScale(0.4);
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$owner = $this->getOwningEntity();
		if($owner instanceof User){
			$session = Base::getInstance()->sessionManager->getSession($owner);
			if($owner->isAlive() and $session->canTakeDamage()){
				if($event instanceof ProjectileHitEntityEvent){
					$entityHit = $event->getEntityHit();
					if($entityHit instanceof User){
						$session = Base::getInstance()->sessionManager->getSession($owner);
						$session->startAgroTimer();
					}
				}
				if($owner->getWorld()->getId() === $this->getWorld()->getId()){
					$this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle());
					$this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound());

					$owner->teleport($event->getRayTraceResult()->getHitVector());

					$owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_CUSTOM, 0));

					$this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle());
					$this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound());
				}
			}
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->isCollided){
			$this->flagForDespawn();
		}
		return parent::entityBaseTick($tickDiff);
	}
}