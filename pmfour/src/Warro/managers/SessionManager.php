<?php

/*
 *
 * Developed by Warro#7777
 * Join Ganja: ganja.bet:19132
 * My Discord: https://discord.gg/vasar
 * Repository: https://github.com/Wqrro/Ganja
 *
 */

namespace Warro\managers;

use pocketmine\player\Player;
use Warro\Base;
use Warro\Session;

class SessionManager
{

	public const RANK = RankManager::DEFAULT;

	public array $sessions = [];
	public static SessionManager $instance;

	public function __construct(private Base $plugin)
	{
		self::$instance = $this;
	}

	public static function getInstance(): self
	{
		return self::$instance;
	}

	public function getSession(Player $player): ?Session
	{
		return $this->sessions[$player->getName()] ?? null;
	}

	public function createSession(Player $player): void
	{
		$session = new Session($player, $this->plugin);
		$this->sessions[$player->getName()] = $session;
	}
}