-- #!sqlite
-- #{ db

-- #    { init.ranks
CREATE TABLE IF NOT EXISTS Ranks(Player VARCHAR(16) NOT NULL,
    RankName VARCHAR(16) NOT NULL,
    PRIMARY KEY(Player));
-- #    }

-- #    { register.player.ranks
-- #    :player string
-- #    :rank string
REPLACE INTO Ranks (Player, RankName) VALUES (:player, :rank);
-- #    }

-- #    { check.ranks
-- #    :player string
SELECT Player FROM Ranks WHERE Player=:player;
-- #    }

-- #    { get.ranks
-- #    :player string
SELECT * FROM Ranks WHERE Player=:player;
-- #    }

-- #    { set.ranks
-- #    :player string
-- #    :rank string
UPDATE Ranks SET RankName=:rank WHERE Player=:player;
-- #    }

-- #    { list.ranks
-- #    :rank string
SELECT Player, RankName FROM Ranks WHERE RankName LIKE :rank ORDER BY RankName;
-- #    }

-- #    { count.ranks
-- #    :rank string
SELECT COUNT (Player) as count FROM Ranks WHERE RankName=:rank;
-- #    }

-- #    { init.stats
CREATE TABLE IF NOT EXISTS Stats(Player VARCHAR(16) NOT NULL,
    Kills INT UNSIGNED DEFAULT 0,
    Deaths INT UNSIGNED DEFAULT 0,
    Killstreak INT UNSIGNED DEFAULT 0,
    BestKillstreak INT UNSIGNED DEFAULT 0,
    PRIMARY KEY(Player));
-- #    }

-- #    { register.player.stats
-- #    :player string
-- #    :kills int
-- #    :deaths int
-- #    :killstreak int
-- #    :bestkillstreak int
INSERT OR REPLACE INTO Stats(Player, Kills, Deaths, Killstreak, BestKillstreak) VALUES (:player, :kills, :deaths, :killstreak, :bestkillstreak);
-- #    }

-- #    { check.stats
-- #    :player string
SELECT Player FROM Stats WHERE Player=:player;
-- #    }

-- #    { set.kills
-- #    :player string
-- #    :kills int
UPDATE Stats SET Kills=:kills WHERE Player=:player;
-- #    }

-- #    { set.deaths
-- #    :player string
-- #    :deaths int
UPDATE Stats SET Deaths=:deaths WHERE Player=:player;
-- #    }

-- #    { set.killstreak
-- #    :player string
-- #    :killstreak int
UPDATE Stats SET Killstreak=:killstreak WHERE Player=:player;
-- #    }

-- #    { set.bestkillstreak
-- #    :player string
-- #    :killstreak int
UPDATE Stats SET BestKillstreak=:killstreak WHERE Player=:player;
-- #    }

-- #    { get.kills
-- #    :player string
SELECT Kills FROM Stats WHERE Player=:player;
-- #    }

-- #    { get.deaths
-- #    :player string
SELECT Deaths FROM Stats WHERE Player=:player;
-- #    }

-- #    { get.killstreak
-- #    :player string
SELECT Killstreak FROM Stats WHERE Player=:player;
-- #    }

-- #    { get.bestkillstreak
-- #    :player string
SELECT BestKillstreak FROM Stats WHERE Player=:player;
-- #    }

-- #    { get.both.killstreak
-- #    :player string
SELECT Killstreak, BestKillstreak FROM Stats WHERE Player=:player;
-- #    }

-- #    { get.kills.deaths
-- #    :player string
SELECT Kills, Deaths FROM Stats WHERE Player=:player;
-- #    }

-- #    { get.all.stats
-- #    :player string
SELECT Kills, Deaths, Killstreak, BestKillstreak FROM Stats WHERE Player=:player;
-- #    }

-- #    { top.kills
-- #    :desc int
SELECT Player, Kills FROM Stats ORDER BY Kills DESC LIMIT :desc;
-- #    }

-- #    { top.bestkillstreak
-- #    :desc int
SELECT Player, BestKillstreak FROM Stats ORDER BY BestKillstreak DESC LIMIT :desc;
-- #    }

-- #}