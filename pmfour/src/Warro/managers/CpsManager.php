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

namespace Warro\managers;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_filter;
use function array_pop;
use function array_unshift;
use function count;
use function microtime;
use function round;

class CpsManager
{

	private array $clicks = [];

	public function doesPlayerExist(Player $player): bool
	{
		return isset($this->clicks[$player->getName()]);
	}

	public function addPlayer(Player $player)
	{
		if (!$this->doesPlayerExist($player)) {
			$this->clicks[$player->getName()] = [];
		}
	}

	public function removePlayer(Player $player)
	{
		if ($this->doesPlayerExist($player)) {
			unset($this->clicks[$player->getName()]);
		}
	}

	public function addClick(Player $player)
	{
		array_unshift($this->clicks[$player->getName()], microtime(true));
		if (count($this->clicks[$player->getName()]) > 20) {
			array_pop($this->clicks[$player->getName()]);
		}

		$player->sendTip(TextFormat::DARK_GREEN . 'CPS' . TextFormat::DARK_GRAY . ': ' . TextFormat::WHITE . abs($this->getCps($player)));
	}

	public function getCps(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1): float
	{
		if (!$this->doesPlayerExist($player) or empty($this->clicks[$player->getName()])) {
			return 0.0;
		}

		$mt = microtime(true);

		return round(count(array_filter($this->clicks[$player->getName()], static function (float $t) use ($deltaTime, $mt): bool {
				return ($mt - $t) <= $deltaTime;
			})) / $deltaTime, $roundPrecision);
	}
}