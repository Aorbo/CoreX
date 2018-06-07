<?php
/*
*
*  _____                 _            _             
* |_   _|               | |          | |            
*   | |  _ __ ___  _ __ | | __ _  ___| |_ ___  _ __ 
*   | | | '_ ` _ \| '_ \| |/ _` |/ __| __/ _ \| '__|
*  _| |_| | | | | | |_) | | (_| | (__| || (_) | |   
* |_____|_| |_| |_| .__/|_|\__,_|\___|\__\___/|_|   
*                 | |                               
*                 |_|                               
*
* Implactor (1.4.x | 1.5.x)
* A plugin with some features for Minecraft: Bedrock!
* --- = ---
*
* Team: ImpladeDeveloped
* 2018 (c) Zadezter
*
*/
declare(strict_types=1);
namespace Implactor\tasks;

use pocketmine\scheduler\Task;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat as IR;

use Implactor\MainIR;

class ClearLaggTask extends Task {
	
      /** @var MainIR $plugin */
         private $plugin;	
	
    public function __construct(MainIR $plugin){
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }
    
    public function onRun(int $currentTick) : void{
            $this->plugin->clearItems();
            $this->plugin->clearMobs();
            $this->plugin->getServer()->broadcastMessage("§8(§a!§8)§r §aAll entities has cleared! \n§f- §6Including a §bcorpses §6and §dbot humans§6!"); 
	    }
    }
