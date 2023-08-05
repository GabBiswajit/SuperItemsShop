<?php

declare(strict_types=1);

namespace Biswajit\libs\davidglitch04\libEco;

use Closure;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use Biswajit\libs\davidglitch04\libEco\Utils\Utils;
use onebone\economyapi\EconomyAPI;
use pocketmine\player\Player;
use pocketmine\Server as PMServer;

final class libEco{
    /**
     * @return array<string, object>
     */
    private static function getEconomy(): array{
        $api = PMServer::getInstance()->getPluginManager()->getPlugin('EconomyAPI');
        if ($api !== null) {
            return [Utils::ECONOMYAPI, $api];
        } else {
            $api = PMServer::getInstance()->getPluginManager()->getPlugin('BedrockEconomy');
            if ($api !== null) {
                return [Utils::BEDROCKECONOMYAPI, $api];
            } else{
                return [null];
            }
        }
    }
    
    public function isInstall(): bool{
        return !is_null($this->getEconomy()[0]);
    }

    /**
     * @return int
     */
    public static function myMoney(Player $player, Closure $callback): void{
        if (self::getEconomy()[0] === Utils::ECONOMYAPI) {
            $money = self::getEconomy()[1]->myMoney($player);
            assert(is_float($money));
            $callback($money);
        } elseif (self::getEconomy()[0] === Utils::BEDROCKECONOMYAPI) {
            self::getEconomy()[1]->getAPI()->getPlayerBalance($player->getName(), ClosureContext::create(static function (?int $balance) use ($callback): void {
                $callback($balance ?? 0);
            }));
        }
    }

    /**
     * @param Player $player
     * @param float $amount
     * @return void
     */
    public static function addMoney(Player $player, float $amount): void{
        if (self::getEconomy()[0] === Utils::ECONOMYAPI) {
            self::getEconomy()[1]->addMoney($player, $amount);
        } elseif (self::getEconomy()[0] === Utils::BEDROCKECONOMYAPI) {
            self::getEconomy()[1]->getAPI()->addToPlayerBalance($player->getName(), (int) $amount);
        }
    }

    /**
     * @param Player $player
     * @param float $amount
     * @param Closure|null $callback
     * @return void
     */
    public static function reduceMoney(Player $player, float $amount, ?Closure $callback = null): void{
        if (self::getEconomy()[0] === Utils::ECONOMYAPI) {
            $callback(self::getEconomy()[1]->reduceMoney($player, $amount) === EconomyAPI::RET_SUCCESS);
        } elseif (self::getEconomy()[0] === Utils::BEDROCKECONOMYAPI) {
            self::getEconomy()[1]->getAPI()->subtractFromPlayerBalance($player->getName(), (int)ceil($amount), $callback ? ClosureContext::create($callback) : null);
        }
    }
}
