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

use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\{EntityDataHelper, EntityFactory, Location};
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;
use Warro\commands\{Kill, Nick, Ping, Rank, Rekit, Spawn};
use Warro\entities\{DeadEntity, VasarItemEntity, VasarPearl, VasarPotion};
use Warro\generator\VoidGenerator;
use Warro\items\VasarItemEnderPearl;
use Warro\items\VasarItemSplashPotion;
use Warro\listeners\{PlayerListener, WorldListener};
use Warro\managers\CpsManager;
use Warro\managers\RankManager;
use Warro\managers\SessionManager;
use Warro\tasks\{CombatTask, EnderPearlTask};

class Base extends PluginBase{

	public static Base $instance;

	public ?DataConnector $database = null;

	public ?Utils $utils = null;
	public ?Forms $forms = null;
	public ?SessionManager $sessionManager = null;
	public ?RankManager $rankManager = null;
	public ?CpsManager $cpsManager = null;

	public static function getInstance() : Base{
		return self::$instance;
	}

	public function onLoad() : void{
		self::$instance = $this;

		/*
		 *
		 * 1. Make sure you have DEVirion to be able to load Virions (view the link below)
		 * DEVirion by poggit: https://poggit.pmmp.io/p/DEVirion/1.2.8
		 *
		 * 2. Make sure you have libasynql in your Virions folder (view the link below)
		 * libasynql by poggit: https://poggit.pmmp.io/ci/poggit/libasynql/libasynql
		 *
		 * Open an issue on the repository or DM me on Discord at Warro#7777 if you have any questions
		 *
		 */

		$this->doOverride();
		$this->getServer()->getNetwork()->setName(TextFormat::DARK_GREEN . Variables::NAME . TextFormat::GREEN . TextFormat::GRAY);
	}

	public function onEnable() : void{
		$this->utils = new Utils();
		$this->forms = new Forms();
		$this->sessionManager = new SessionManager($this);
		$this->rankManager = new RankManager();
		$this->cpsManager = new CpsManager();

		$generators = ['void' => VoidGenerator::class];
		foreach($generators as $name => $class){
			GeneratorManager::getInstance()->addGenerator($class, $name, fn() => null, true);
		}

		$this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new WorldListener(), $this);

		$this->getScheduler()->scheduleRepeatingTask(new EnderPearlTask($this), 1);
		$this->getScheduler()->scheduleRepeatingTask(new CombatTask($this), 20);

		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('kill'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('me'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('defaultgamemode'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('difficulty'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('spawnpoint'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('setworldspawn'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('title'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('seed'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('particle'));
		$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand('clear'));

		$this->getServer()->getWorldManager()->loadWorld(Variables::SPAWN);
		$this->getServer()->getWorldManager()->loadWorld(Variables::NODEBUFF_FFA_ARENA);

		foreach($this->getServer()->getWorldManager()->getWorlds() as $world){
			$world->setAutoSave(false);
			$world->setTime(World::TIME_DAY);
			$world->stopTime();
			foreach($world->getEntities() as $entity){
				$entity->flagForDespawn();
			}

			if($world->getFolderName() === Variables::SPAWN){
				$this->utils->spawnLocation = new Location(0.5, 101, 0.5, $world, 0, 180);
			}else{
				$this->utils->nodebuffLocation = new Location(100.5, 101, 100.5, $world, 0, 0);
			}
		}

		if(is_null($this->utils->spawnLocation) or is_null($this->utils->nodebuffLocation)){
			$this->getLogger()->critical('Make sure you have your Worlds loaded!');
			$this->getServer()->shutdown();
		}

		$this->getServer()->getCommandMap()->registerAll(Variables::NAME, [
			new Ping(),
			new Nick($this),
			new Rank($this),
			new Kill(),
			new Rekit($this),
			new Spawn($this),
		]);

		$this->database = libasynql::create($this, ['type' => 'sqlite', 'sqlite' => ['file' => $this->getDataFolder() . 'sqlite.db']], ['sqlite' => 'sqlite.sql']);
		$this->database->executeGeneric('db.init.ranks', [], null, function(SqlError $error_) use (&$error){
			$error = $error_;
		});
		$this->database->waitAll();

		$this->database = libasynql::create($this, ['type' => 'sqlite', 'sqlite' => ['file' => $this->getDataFolder() . 'sqlite.db']], ['sqlite' => 'sqlite.sql']);
		$this->database->executeGeneric('db.init.stats', [], null, function(SqlError $error_) use (&$error){
			$error = $error_;
		});
		$this->database->waitAll();

		$this->getLogger()->notice(TextFormat::DARK_AQUA . 'Created by Warro#7777 - discord.gg/vasar');
	}

	public function onDisable() : void{
		foreach($this->getServer()->getOnlinePlayers() as $players){
			$players->kick(TextFormat::GREEN . 'Restarting' . TextFormat::DARK_GREEN . ' - ' . Variables::NAME);
		}

		foreach($this->getServer()->getWorldManager()->getWorlds() as $level){
			foreach($level->getEntities() as $entity){
				if(!$entity instanceof User){
					$entity->close();
				}
			}
		}
	}

	private function doOverride() : void{

	}
}