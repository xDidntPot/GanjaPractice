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

class RankManager{

	private array $ranks = [];

	public const int DEFAULT = 0;
	public const int VIP = 1;
	public const int OWNER = 2;

	public function __construct(){
		$this->ranks [self::DEFAULT] = $this->getRankAsString(self::DEFAULT);
		$this->ranks [self::VIP] = $this->getRankAsString(self::VIP);
		$this->ranks [self::OWNER] = $this->getRankAsString(self::OWNER);
	}

	public function getRanks() : array{
		return $this->ranks;
	}

	public function doesRankExist(int $rank) : bool{
		return isset($this->ranks[$rank]);
	}

	public function getRankFromString(string $rank) : ?int{
		return match (strtolower($rank)) {
			default => null,
			'default' => RankManager::DEFAULT,
			'vip' => RankManager::VIP,
			'owner' => RankManager::OWNER,
		};
	}

	public function getRankAsString(int $rank) : ?string{
		return match ($rank) {
			default => null,
			RankManager::DEFAULT => 'Default',
			RankManager::VIP => 'VIP',
			RankManager::OWNER => 'Owner',
		};
	}
}