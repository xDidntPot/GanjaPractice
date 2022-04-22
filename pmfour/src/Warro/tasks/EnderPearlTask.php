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

namespace Warro\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use Warro\Base;
use Warro\User;
use Warro\Variables;
use function count;

class EnderPearlTask extends Task
{

	public function __construct(private Base $plugin)
	{
	}

	public function onRun(): void
	{
		if (count($this->plugin->utils->pearlPlayer) > 0) {
			foreach ($this->plugin->utils->pearlPlayer as $name => $time) {
				$player = Server::getInstance()->getPlayerExact($name);
				if ($player instanceof User) {
					if ($time <= 0) {
						if ($this->plugin->utils->isInPearlCooldown($player)) {
							$this->plugin->utils->setPearlCooldown($player, false, true);
						}
						$player->getXpManager()->setXpAndProgress(0, 0);
						return;
					} else {
						$percent = floatval($time / Variables::PEARL_COOLDOWN);
						$player->getXpManager()->setXpAndProgress(intval($time / 20), $percent);
					}
					$this->plugin->utils->pearlPlayer[$name]--;
				}
			}
		}
	}
}