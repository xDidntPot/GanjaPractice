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

namespace Warro;

use pocketmine\entity\Location;
use pocketmine\event\entity\{EntityDamageByChildEntityEvent, EntityDamageByEntityEvent, EntityDamageEvent};
use pocketmine\form\Form;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\player\Player;
use pocketmine\player\PlayerInfo;
use pocketmine\Server;
use function mt_getrandmax;
use function mt_rand;
use function sqrt;

class User extends Player{

	private int|float $openForm;

	public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag){
		parent::__construct($server, $session, $playerInfo, $authenticated, $spawnLocation, $namedtag);
		$this->openForm = microtime(true);
	}

	public function canBeCollidedWith() : bool{
		$session = Base::getInstance()->sessionManager->getSession($this);

		if(is_null($session)){
			return false;
		}

		if(!$session->canTakeDamage()){
			return false;
		}
		return parent::canBeCollidedWith();
	}

	public function sendForm(Form $form) : void{
		if(is_float($this->openForm) and $this->openForm + 0.25 <= microtime(true)){
			$this->openForm = microtime(true);
			parent::sendForm($form);
		}
	}

	protected function onHitGround() : ?float{
		$fallBlockPos = $this->location->floor();
		$fallBlock = $this->getWorld()->getBlock($fallBlockPos);
		if(count($fallBlock->getCollisionBoxes()) === 0){
			$fallBlockPos = $fallBlockPos->down();
			$fallBlock = $this->getWorld()->getBlock($fallBlockPos);
		}
		$newVerticalVelocity = $fallBlock->onEntityLand($this);

		$damage = $this->calculateFallDamage($this->fallDistance);
		if($damage > 0){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev);
		}

		return $newVerticalVelocity;
	}

	public function attack(EntityDamageEvent $source) : void{
		$cause = $source->getCause();
		if($source instanceof EntityDamageByEntityEvent){
			$damager = $source->getDamager();
			if($damager instanceof $this){
				if($cause === EntityDamageEvent::CAUSE_ENTITY_ATTACK and $this->attackTime > 0){
					$source->cancel();
				}
			}
		}
		parent::attack($source);
		if($source->isCancelled()){
			return;
		}
		if($source instanceof EntityDamageByEntityEvent){
			$session = Base::getInstance()->sessionManager->getSession($this);

			if(is_null($session)){
				return;
			}

			$damager = $source->getDamager();
			if($damager instanceof $this and $cause === EntityDamageEvent::CAUSE_ENTITY_ATTACK){
				$this->attackTime = $session->attackCooldown;
			}elseif($source instanceof EntityDamageByChildEntityEvent){
				$this->attackTime = intval($session->attackCooldown / 2);
			}
		}
	}

	public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4) : void{
		$session = Base::getInstance()->sessionManager->getSession($this);

		if(is_null($session)){
			return;
		}

		[$horizontal, $vertical] = [$session->hKnockBack, $session->vKnockBack];

		if($session->hasLastDamagePosition()){
			$position = $session->getLastDamagePosition();
			if($position instanceof Vector3){
				$dist = $this->getPosition()->getY() - $position->getY();
				if(!$this->isOnGround() and $dist >= $session->maxDistanceKnockBack){
					$vertical -= $dist * $session->heightLimiterKnockBack;
				}
			}
		}

		if($session->hasAgroTimerStarted()){
			$horizontal *= 0.85;
			$vertical *= 0.85;
		}

		$session->resetAgroTimer();

		$f = sqrt($x * $x + $z * $z);
		if($f <= 0){
			return;
		}
		if(mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()){
			$f = 1 / $f;

			$motion = clone $this->motion;

			$motion->x /= 2;
			$motion->y /= 2;
			$motion->z /= 2;
			$motion->x += $x * $f * $horizontal;
			$motion->y += $vertical;
			$motion->z += $z * $f * $horizontal;

			if($motion->y > $vertical){
				$motion->y = $vertical;
			}

			$this->setMotion($motion);
		}
	}
}