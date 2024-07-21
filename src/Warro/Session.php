<?php

/*
 *
 * Developed by Warro#7777
 * Join Ganja: ganja.bet:19132
 * My Discord: https://discord.gg/vasar
 * Repository: https://github.com/Wqrro/Ganja
 *
 */

namespace Warro;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Warro\managers\RankManager;
use Warro\managers\SessionManager;
use Warro\tasks\local\BaseTask;

class Session{

	public int $rank = RankManager::DEFAULT;

	private int $respawnTimer = -1;
	private int $agroTicks = -1;
	private ?string $damager = null;
	private ?Position $lastDamagePosition = null;
	private bool $takeDamage = true;

	public float $hKnockBack = 0.0;
	public float $vKnockBack = 0.0;
	public float $maxDistanceKnockBack = 3;
	public float $heightLimiterKnockBack = 0.026;
	public int $attackCooldown = 10;

	public int $kills = 0;
	public int $deaths = 0;
	public int $killstreak = 0;

	public function __construct(public Player $player, private Base $plugin){
		$this->isRegisteredRanks(function(array $rows) : void{
			if(count($rows) < 1){
				$this->registerRanks();
			}
			$this->loadRanks();
		});
	}

	public function onJoin() : void{
		$this->plugin->getScheduler()->scheduleRepeatingTask(new BaseTask($this, $this->player), 1);

		$this->plugin->utils->teleport($this->player, Variables::TELEPORT_LOBBY, true);

		Base::getInstance()->cpsManager->addPlayer($this->player);

		$this->player->getHungerManager()->setEnabled(false);
		$this->player->sendMessage(TextFormat::EOL . TextFormat::GRAY . '-#-' .
			TextFormat::EOL . TextFormat::DARK_GREEN . Variables::NAME .
			TextFormat::EOL . TextFormat::AQUA . Variables::DISCORD .
			TextFormat::EOL . TextFormat::GRAY . '-#-' .
			TextFormat::EOL . TextFormat::EOL);
	}

	public function onQuit(PlayerQuitEvent $event) : void{
		if($this->hasDamager() and $event->getQuitReason() === 'client disconnect'){
			$this->player->attack(new EntityDamageEvent($this->player, EntityDamageEvent::CAUSE_SUICIDE, 1000));
		}

		if(isset(Base::getInstance()->utils->taggedPlayer[$this->player->getName()])){
			unset(Base::getInstance()->utils->taggedPlayer[$this->player->getName()]);
		}
		if(isset(Base::getInstance()->utils->pearlPlayer[$this->player->getName()])){
			unset(Base::getInstance()->utils->pearlPlayer[$this->player->getName()]);
		}
		if(isset(Base::getInstance()->utils->chatCooldown[$this->player->getName()])){
			unset(Base::getInstance()->utils->chatCooldown[$this->player->getName()]);
		}
		if(Base::getInstance()->cpsManager->doesPlayerExist($this->player)){
			Base::getInstance()->cpsManager->removePlayer($this->player);
		}

		$this->saveAll(function() : void{
			unset(SessionManager::getInstance()->sessions[$this->player->getName()]);
		});
	}

	public function isRegisteredRanks(callable $callable) : void{
		Base::getInstance()->database->executeSelect('db.check.ranks', ['player' => $this->player->getName()], function(array $rows) use ($callable) : void{
			$callable($rows);
		});
	}

	public function registerRanks() : void{
		Base::getInstance()->database->executeGeneric('db.register.player.ranks', ['player' => $this->player->getName(), 'rank' => $this->getRank(true)]);
	}

	public function loadRanks() : void{
		Base::getInstance()->database->executeSelect('db.get.ranks', ['player' => $this->player->getName()], function($rows){
			foreach($rows as $row){
				$rank = Base::getInstance()->rankManager->getRankFromString($row['RankName']);
				if(!is_null($rank)){
					$this->rank = $rank;
				}
			}
		});
	}

	public function saveRanks(?callable $callable) : void{
		Base::getInstance()->database->executeGeneric('db.set.ranks', ['player' => $this->player->getName(), 'rank' => $this->getRank(true)], $callable);
	}

	public function loadAll() : void{
		$this->loadRanks();
	}

	public function saveAll(?callable $callable) : void{
		$this->saveRanks($callable);
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function setRank(int $int = SessionManager::RANK){
		$this->rank = $int;
		$this->player->setNameTag(Base::getInstance()->utils->getTagFormat($this->player));
		$this->player->sendMessage(TextFormat::GOLD . 'Your Rank is now ' . $this->getRank(true) . '.');
	}

	public function getRank(bool $asString = false) : int|string{
		if($asString){
			return Base::getInstance()->rankManager->getRankAsString($this->rank);
		}
		return $this->rank;
	}

	public function startRespawnTimer(int $time = Variables::RESPAWN_TIMER){
		$this->respawnTimer = $time;
	}

	public function decreaseRespawnTimer(){
		if($this->hasRespawnTimerStarted()){
			$this->respawnTimer--;
			if($this->respawnTimer <= 0){
				$this->resetRespawnTimer();
				$this->setTakeDamage();
				$this->setDamager();
				Base::getInstance()->utils->teleport($this->player, Variables::TELEPORT_LOBBY, true);
			}
		}
	}

	public function resetRespawnTimer(){
		$this->respawnTimer = -1;
	}

	public function hasRespawnTimerStarted() : bool{
		return $this->respawnTimer !== -1;
	}

	public function startAgroTimer(int $time = Variables::AGRO_TICKS){
		$this->agroTicks = $time;
	}

	public function decreaseAgroTimer(){
		if($this->hasAgroTimerStarted()){
			$this->agroTicks--;
			if($this->agroTicks <= 0){
				$this->resetAgroTimer();
			}
		}
	}

	public function resetAgroTimer(){
		$this->agroTicks = -1;
	}

	public function hasAgroTimerStarted() : bool{
		return $this->agroTicks !== -1;
	}

	public function setDamager(?string $variable = null){
		if(is_string($this->damager)){
			$currentDamager = Server::getInstance()->getPlayerExact($this->damager);
			if($currentDamager instanceof User){
				$currentDamager->sendData(
					[$this->player],
					[EntityMetadataProperties::NAMETAG =>
						new StringMetadataProperty($currentDamager->getNameTag())]);
			}
		}

		$this->damager = $variable;

		if(is_string($variable)){
			$newDamager = Server::getInstance()->getPlayerExact($variable);
			if($newDamager instanceof User){
				$newDamager->sendData(
					[$this->player],
					[EntityMetadataProperties::NAMETAG =>
						new StringMetadataProperty(TextFormat::DARK_RED . $newDamager->getDisplayName())]);
			}
		}
	}

	public function getDamager() : ?string{
		return $this->damager;
	}

	public function hasDamager() : bool{
		return $this->damager !== null;
	}

	public function setLastDamagePosition(?Position $variable = null){
		$this->lastDamagePosition = $variable;
	}

	public function getLastDamagePosition() : ?Position{
		return $this->lastDamagePosition;
	}

	public function hasLastDamagePosition() : bool{
		return $this->lastDamagePosition !== null;
	}

	public function setTakeDamage(bool $variable = true){
		$this->takeDamage = $variable;
	}

	public function canTakeDamage() : bool{
		return $this->takeDamage;
	}

	public function getKills() : int{
		return $this->kills;
	}

	public function addKill() : void{
		$this->kills++;
	}

	public function getDeaths() : int{
		return $this->deaths;
	}

	public function addDeath() : void{
		$this->deaths++;
	}

	public function getKillstreak() : int{
		return $this->killstreak;
	}

	public function addToKillstreak() : void{
		$this->killstreak++;
	}

	public function resetKillstreak() : void{
		$this->killstreak = 0;
	}
}
