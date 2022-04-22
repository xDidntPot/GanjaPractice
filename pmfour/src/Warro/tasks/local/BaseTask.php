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

namespace Warro\tasks\local;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use Warro\Base;
use Warro\Session;
use Warro\User;

class BaseTask extends Task
{

	private int $tick = 0;

	public function __construct(Base $plugin, private Session $session, private Player|User $player)
	{
	}

	public function onRun(): void
	{
		if (!$this->player->isConnected()) {
			$this->getHandler()->cancel();
			return;
		}

		if ($this->session->hasRespawnTimerStarted()) {
			if ($this->tick % 20 === 0) {
				$this->session->decreaseRespawnTimer();
			}
		}

		if ($this->session->hasAgroTimerStarted()) {
			$this->session->decreaseAgroTimer();
		}

		$this->tick++;
	}
}