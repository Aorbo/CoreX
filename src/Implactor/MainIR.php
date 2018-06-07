<?php
/*
*                              
*
* CoreX (1.4.x | 1.5.x)
* A plugin with some features for Minecraft: Bedrock!
* --- = ---
*
* Team: ImpladeDeveloped
* 2018 (c) xXCaulDevsYT
*
*/
declare(strict_types=1);
namespace CoreX;

use pocketmine\{
	Server, Player
};
use pocketmine\nbt\tag\{
	CompoundTag, ListTag, DoubleTag, FloatTag
};
use pocketmine\plugin\{
	PluginBase, Plugin
};
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as IR;
use pocketmine\scheduler\Task;
use pocketmine\event\player\{
	PlayerLoginEvent, PlayerJoinEvent, PlayerQuitEvent, PlayerDeathEvent, PlayerRespawnEvent, PlayerMoveEvent, PlayerPreLoginEvent
};
use pocketmine\command\{
	CommandSender, Command
};
use pocketmine\level\{
	Location, Position, Level
};
use pocketmine\entity\{
	Entity, EffectInstance, Creature, Human, Effect
};
use pocketmine\math\Vector3;
use pocketmine\level\particle\{
	DestroyBlockParticle as FrostBloodParticle, AngryVillagerParticle, FlameParticle
};
use pocketmine\block\Block;
use pocketmine\event\entity\{
	EntityDamageEvent, EntityDamageByEntityEvent, EntitySpawnEvent
};
use pocketmine\level\sound\{
	EndermanTeleportSound, DoorCrashSound, AnvilBreakSound, GhastSound, BlazeShootSound
};
use pocketmine\item\Item;

// PARTICLES \\
use Implactor\particles\HubParticle;
use Implactor\particles\BotParticle;
use Implactor\particles\DeathParticle;
use Implactor\particles\TeleportHubParticle;
use Implactor\particles\WildTeleportParticle;

// NPCs | Bots \\
use Implactor\npc\DeathHumanEntityTask;
use Implactor\npc\DeathHumanClearEntityTask;
use Implactor\npc\bot\BotHuman;
use Implactor\npc\bot\BotTask;
use Implactor\npc\bot\BotParticleTask;
use Implactor\npc\bot\BotSneakTask;
use Implactor\npc\bot\BotUnsneakTask;
use Implactor\npc\bot\BotWalkingTask;
use Implactor\npc\bot\BotListener;

// TASKS \\
use Implactor\tasks\ClearLaggTask;
use Implactor\tasks\HubTask;
use Implactor\tasks\AntiAdvertising;
use Implactor\tasks\AntiSwearing;

class MainIR extends PluginBase implements Listener {
	
	/** @var array $freeze */
	private $freeze = [];
	/** @var array $vanish */
	private $vanish = [];
	
	public function onLoad(): void{
		$this->getLogger()->info(IR::BLUE . "Loading all resources and codes on CoreX plugin...");
	}
	
	public function onEnable(): void{
		$this->getLogger()->info(IR::GREEN . "CoreX plugin is now online!");
		$this->getScheduler()->scheduleRepeatingTask(new HubParticle($this, $this), 20);
                // EVENTS \\
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getPluginManager()->registerEvents(new BotListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new AntiAdvertising($this), $this);
                $this->getServer()->getPluginManager()->registerEvents(new AntiSwearing($this), $this);
           
                // ENTITY \\
		Entity::registerEntity(DeathHumanEntityTask::class, true);
		Entity::registerEntity(BotHuman::class, true);
		
		// CLEAR LAGG | 6 MINUTES \\
		if(is_numeric(360)){ 
                $this->getScheduler()->scheduleRepeatingTask(new ClearLaggTask($this, $this), 360 * 20);
              }
        }
	
	public function onDisable(): void{
		$this->getLogger()->info(IR::RED . "CoreX plugin is now offline!");
		$this->getServer()->shutdown();
	}
	
	public function onPlayerLogin(PlayerLoginEvent $ev): void{
		$ev->getPlayer()->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
		$ev->getPlayer()->setHealth(40);
		$ev->getPlayer()->setMaxHealth(40);
	}
	
	public function onPlayerPreLogin(PlayerPreLoginEvent $ev) : void{
               $player = $ev->getPlayer();
		
                // WHITELIST REASON \\
		if(!$this->getServer()->isWhitelisted($player->getName())){
			$ev->setKickMessage("§l§7[ §cNOTICE §7]\n §eThis server is currently on §fmaintanence §emode!");
			$ev->setCancelled(true);
			
                        // BANNED REASON \\
			if(!$this->getServer()->isBanned($player->getName())){
				$ev->setKickMessage("§l§bCube§eX§d Network\n §r§aYou've Been Banned From CubeX Network\n§7Buy an Unban At Buycraft");
				$ev->setCancelled(true);
			}
		}
	}
	
	public function onPlayerJoin(PlayerJoinEvent $ev): void{
		$player = $ev->getPlayer();
		$ev->setJoinMessage("§l§8[§a+§8]§r §a{$player->getName()}");
		$player->setHealth(40);
		$player->setMaxHealth(40);
                $player->setGamemode(Player::SURVIVAL);
		$player->getLevel()->addSound(new EndermanTeleportSound($player));
		
                // A PLUGIN MESSAGE WHEN THEY JOINED \\
                $player->sendMessage("§7[§aM§6C§7]§r §bThis server is using CoreX plugin!");
	}
	
	public function onHit(EntityDamageEvent $ev): void{
		if ($ev->getEntity() instanceof Player) {
			if ($ev instanceof EntityDamageByEntityEvent) {
				$ev->getEntity()->getLevel()->addParticle(new FrostBloodParticle($ev->getEntity(), Block::get(57)));
			}
		}
	}
	
	public function onMove(PlayerMoveEvent $ev) : void{
		$player = $ev->getPlayer();
		$player->getLevel()->addParticle(new AngryVillagerParticle($player));
		$player->getLevel()->addParticle(new FlameParticle($player));
		/** FREEZE EVENT **/
		if(in_array($player->getName(), $this->freeze)) $ev->setCancelled(true);
                if(in_array($player->getName(), $this->freeze)) $player->sendTip("§bYou are §ffrozen§b, ". IR::YELLOW . $player->getName() ."§b!\n§aDo §b/freeze §ato unfrozen yourself!");
	} 
	
	public function onPlayerQuit(PlayerQuitEvent $ev): void{
		$player = $ev->getPlayer();
		$ev->setQuitMessage("§l§8[§c-§8]§r §c{$player->getName()}");   
		$player->getLevel()->addSound(new DoorCrashSound($player));
	}
	
	public function onPlayerDeath(PlayerDeathEvent $ev): void{
		$player = $ev->getPlayer();
		$this->getScheduler()->scheduleDelayedTask(new DeathParticle($this, $player), 20);
		$player->getLevel()->addSound(new AnvilBreakSound($player));
		$player->getLevel()->addSound(new GhastSound($player));
		
                // DEATH ANIMATION \\
		$nbt = new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $player->getX()),
				new DoubleTag("", $player->getY() - 1),
				new DoubleTag("", $player->getZ())
			]),
			new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			new ListTag("Rotation", [
				new FloatTag("", 2),
				new FloatTag("", 2)
			])
		]);
		$nbt->setTag($player->namedtag->getTag("Skin"));
		$deathpl = new DeathHumanEntityTask($player->getLevel(), $nbt);
		$deathpl->getDataPropertyManager()->setBlockPos(DeathHumanEntityTask::DATA_PLAYER_BED_POSITION, new Vector3($player->getX(), $player->getY(), $player->getZ()));
		$deathpl->setPlayerFlag(DeathHumanEntityTask::DATA_PLAYER_FLAG_SLEEP, true);
		$deathpl->setNameTag("§7[§cDead§7]§r\n§f" .$player->getName(). "");
		$deathpl->setNameTagAlwaysVisible(false);
		$deathpl->spawnToAll();
		$this->getScheduler()->scheduleDelayedTask(new DeathHumanClearEntityTask($this, $npc, $player), 20);
	}
	
	public function onDamage(EntityDamageEvent $ev) : void{
		$entity = $ev->getEntity();
		if($entity instanceof Player){
			if($ev->getCause() === EntityDamageEvent::CAUSE_FALL){
				$ev->setCancelled(true);
			}
			if($ev->getCause() !== $ev::CAUSE_FALL){
				if(!$entity instanceof Player) return;
				if($entity->isCreative()) return;
				if($entity->getAllowFlight() == true){
					$entity->setFlying(false);
					$entity->setAllowFlight(false);
					$entity->sendMessage("§l§7(§c!§7)§r §cYour abilities has disabled because got damaged§e...");
					$entity->getLevel()->addParticle(new FrostBloodParticle($ev->getEntity(), Block::get(57)));      
					if($entity instanceof DeathHumanEntityTask) $ev->setCancelled(true);
				}
			}
		}
	}
	
	public function onRespawn(PlayerRespawnEvent $ev) : void{
		$player = $ev->getPlayer();
		$title = "§l§cYOU ARE DEAD";
		$subtitle = "§eOuch, that's hurt so much!";
		$player->addTitle($title, $subtitle);
		$player->setHealth(40);
		$player->setMaxHealth(40);
                /** SIZE EVENT **/
		if(!empty($this->size[$player->getName()])){
                $size = $this->size[$player->getName()];
                $player->setScale($size);
            }
        }
	
	public function onEntitySpawn(EntitySpawnEvent $ev){
		$entity = $ev->getEntity();
		if($entity instanceof BotHuman){
			$this->getScheduler()->scheduleRepeatingTask(new BotTask($this, $entity), 200);
		}
	}
	
        // BOT HUMAN \\
	public function spawnBot(Player $player, string $name): void{
		$nbt = Entity::createBaseNBT($player, null, $player->getYaw(), $player->getPitch());
		$nbt->setTag($player->namedtag->getTag("Skin"));
		$bot = new BotHuman($player->getLevel(), $nbt);
		$bot->setNameTag("§7[§bBot§7]§r\n§f" .$name. "");
		$bot->setNameTagAlwaysVisible(true);
		$bot->spawnToAll();
	} 
	
        // IMPLACTOR COMMANDS \\
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(strtolower($command->getName()) == "hub") {
			if($sender instanceof Player){
				if($sender->hasPermission("implactor.hub")) {
                                        $this->getScheduler()->scheduleRepeatingTask(new HubTask($this, $sender), 20);
					return true;
				}
			}
		}
		if(strtolower($command->getName()) == "sethub") {
			if($sender instanceof Player){      
				if($sender->hasPermission("implactor.sethub")) {                       	   
					$sender->getLevel()->setSpawnLocation($sender);
					$sender->sendMessage(IR::YELLOW . "You have successfully set a main hub!");
					return true;
				}
			}
		}
                if(strtolower($command->getName()) == "size") {
			if($sender instanceof Player){
			        if($sender->hasPermission("implactor.size")){
                                       if(isset($args[0])){
                                       if(is_numeric($args[0])){
                                       $this->size[$sender->getPlayer()->getName()] = $args[0];
                                       $sender->setScale($args[0]);
                                       $sender->sendMessage("§8(§a!§8)§r §eYour size is now changed to ".IR::WHITE . $args[0]."§c!");
                                     }elseif($args[0] == "reset"){
                                            if(!empty($this->size[$sender->getPlayer()->getName()])){
                                            unset($this->size[$sender->getPlayer()->getName()]);
                                            $sender->setScale(1);
                                            $sender->sendMessage("§8(§c!§8)§r §eYour size is back to normal!");
                                          }else{
                                            $sender->sendMessage("§8(§c!§8)§r §6Your size is now reseted!");
                                           }
                                         }else{
                                            $sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /size §e<[size]|reset>");
					    return false;
                                            }
					   return true;
				       }
                                }
                        }
                }
		if(strtolower($command->getName()) == "fly") {
			if($sender instanceof Player){
				if($sender->hasPermission("implactor.fly")) {                     	   
					if(!$sender->getAllowFlight()){
						$sender->setAllowFlight(true);
						$sender->sendMessage("§8§l(§a!§8)§r §7You have §aenabled §7your fly ability!");
					}else{
						$sender->setAllowFlight(false);
						$sender->setFlying(false);
						$sender->sendMessage("§8§l(§c!§8)§r §7You have §cdisabled §7your fly ability!");
					}
				}else{
					$sender->sendMessage("§cYou have no permission allowed to use §fFly §ccommand§e!");
					return false;
				}
				return true;
			}
		}
		if(strtolower($command->getName()) == "gmc") {
			if(!$sender instanceof Player){
                        $sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /gmc");
                        $sender->sendMessage("§8- §aChange's player gamemode§e: §b/gmc§f <player>");
                        return false;
                        }
			if(!$sender->hasPermission("implactor.gamemode")) {               
       	                $sender->sendMessage("§cYou have no permission allowed to use §aGamemode §ccommand§e!");
                        return false;
                       }                             
                       if(empty($args[0])){
			$sender->setGamemode(Player::CREATIVE);
			$sender->sendMessage("§eChanged your gamemode to §aCreative §emode! \n\n §7- §cDo not use this command again when you're already changed...");
			}
                       $player = $this->getServer()->getPlayer($args[0]);
                       if($this->getServer()->getPlayer($args[0])){
                       $sender->setGamemode(PLAYER::SPECTATOR);
                       $sender->sendMessage("§bYou have changed ". IR::WHITE . $player->getName() . " §bgamemode to §dCreative");
			}else{
			 $sender->sendMessage("§cPlayer not found in server!");
			 return false;
			}
			return true;
		}
		if(strtolower($command->getName()) == "gms") {
			if(!$sender instanceof Player){                         
			$sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /gms");
                        $sender->sendMessage("§8- §aChange's player gamemode§e: §b/gms§f <player>");
                        return false;
                        }
			if(!$sender->hasPermission("implactor.gamemode")) {         
                        $sender->sendMessage("§cYou have no permission allowed to use §aGamemode §ccommand§e!");
                        return false;
                       }                            
                       if(empty($args[0])){
                        $sender->setGamemode(Player::SURVIVAL); 
			$sender->sendMessage("§eChanged your gamemode to §cSurvival §emode! \n\n §7- §cDo not use this command again when you're already changed...");
			}
                       $player = $this->getServer()->getPlayer($args[0]);
                       if($this->getServer()->getPlayer($args[0])){
                       $sender->setGamemode(PLAYER::SPECTATOR);
                       $sender->sendMessage("§bYou have changed ". IR::WHITE . $player->getName() . " §bgamemode to §dSurvival");
                        }else{
                          $sender->sendMessage("§cPlayer not found in server!");
                          return false;
			}
			return true;
		}
		if(strtolower($command->getName()) == "gma") {
			if(!$sender instanceof Player){                
                        $sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /gma");
                        $sender->sendMessage("§8- §aChange's player gamemode§e: §b/gma§f <player>");
                        return false;
                        }
			if(!$sender->hasPermission("implactor.gamemode")) {    
                        $sender->sendMessage("§cYou have no permission allowed to use §aGamemode §ccommand§e!");
                        return false;
                        }                             
                        if(empty($args[0])){
			$sender->setGamemode(Player::ADVENTURE);
			$sender->sendMessage("§eChanged your gamemode to §cAdventure §emode! \n\n §7- §cDo not use this command again when you're already changed...");
			}
                       $player = $this->getServer()->getPlayer($args[0]);
                       if($this->getServer()->getPlayer($args[0])){
                       $sender->setGamemode(PLAYER::SPECTATOR);
                       $sender->sendMessage("§bYou have changed ". IR::WHITE . $player->getName() . " §bgamemode to §dAdventure");
                     }else{
                       $sender->sendMessage("§cPlayer not found in server!");
                       return false;
                      }
                     return true;
		}
		if(strtolower($command->getName()) == "gmspc") {
			if(!$sender instanceof Player){
			$sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /gmspc");
                        $sender->sendMessage("§8- §aChange's player gamemode§e: §b/gmspc§f <player>");
                        return false;
                        }
			if(!$sender->hasPermission("implactor.gamemode")) {
			$sender->sendMessage("§cYou have no permission allowed to use §aGamemode §ccommand§e!");
                        return false;
                       }                             
                       if(empty($args[0])){
                       $sender->setGamemode(Player::SPECTATOR);
                       $sender->sendMessage("§eChanged your gamemode to §bSpectator §emode! \n\n §7- §cDo not use this command again when you're already changed...");
                       return false;
                      }
                     $player = $this->getServer()->getPlayer($args[0]);
                     if($this->getServer()->getPlayer($args[0])){
                     $sender->setGamemode(PLAYER::SPECTATOR);
                     $sender->sendMessage("§bYou have changed ". IR::WHITE . $player->getName() . " §bgamemode to §dSpectator");
                   }else{
                     $sender->sendMessage("§cPlayer not found in server!");
                     return false;
                    }
                   return true;
		}
		if(strtolower($command->getName()) == "nick") {
			if($sender instanceof Player){                          	
				if($sender->hasPermission("implactor.nick")){
					if(count($args) > 0){
						if($args[0] == "off"){
							$sender->setDisplayName($sender->getName());
							$sender->sendMessage("§l§8(§c!§8)§r §7You have set your nickname as §l§cdefault§r§7!");
                                                      }else{
							$sender->setDisplayName($args[0]);
							$sender->sendMessage("§l§8(§a!§8)§r §7You have set your nickname as §l§a" . $args[0] . "§7!");
						}
                                              }else{
						$sender->sendMessage("§l§8(§6!§8)§r §cCommand usage§8:§r§7 /nick <name|off>");
						return false;
					}
                                      }else{
					$sender->sendMessage("§cYou have no permission allowed to use §bNick §ccommand§e!");
					return false;
				}
				return true;
			}
		}
		if(strtolower($command->getName()) == "wild") {
			if($sender instanceof Player){
				if($sender->hasPermission("implactor.wild")){
					$x = mt_rand(1, 999);
					$z = mt_rand(1, 999);
					$y = $sender->getLevel()->getHighestBlockAt($x, $z) + 1;
					$sender->teleport(new Position($x, $y, $z, $sender->getLevel()));
					$sender->addTitle("§7§l[§dWILD§7]§r", "§fRandom Teleporting...");
					$sender->sendMessage("§7-------\n §cTeleporting to random\n §cof §dwild zone§c... §7\n-------");
					$sender->getLevel()->addSound(new BlazeShootSound($sender));
					$this->getScheduler()->scheduleDelayedTask(new TeleportWildParticle($this, $sender), 20);
					return true;
				}
			}
		}
		if(strtolower($command->getName()) == "kill") {
			if(!$sender instanceof Player){
				if(!$sender->hasPermission("implactor.kill")){   
					$sender->sendMessage("§cMove like pain, be steady like a death!");
                                        $sender->getServer()->broadcastMessage(IR::YELLOW . $sender->getPlayer()->getName(). " §chas suicided itself using §fkill command§c!");
					$sender->setHealth(0);
					return true;
				}
			}
		}
		if(strtolower($command->getName()) == "ping") {
			if(!$sender instanceof Player){
				if(!$sender->hasPermission("implactor.ping")){
					$sender->sendMessage($sender->getPlayer()->getName(). "§a's ping status§e,");
					$sender->sendMessage("§b" . $sender->getPing() . "§fms §aon your connection§e!");
				}else{
					$sender->sendMessage("§cYou have no permission allowed to use §fPing §cccommand§e!");
					return false;
				}
				return true;
			}
		}
		if(strtolower($command->getName()) == "clearitem") {
			if(!$sender instanceof Player){
			$sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /clearitem");
                        $sender->sendMessage("§8- §aClear items from player§e: §b/clearitem§f <player>");
                        return false;
                        }
                       if(!$sender->hasPermission("implactor.clearinventory")){       
                       $sender->sendMessage("§cYou have no permission allowed to use §aClear §ccommand§e!");
                       return false;
                      }                                
                      if(empty($args[0])){
                      $sender->getInventory()->clearAll();
                      $sender->sendMessage("§aAll §eitems §awas cleared successfully from your inventory!");
                      return false;
                     }
                    $player = $this->getServer()->getPlayer($args[0]);
                    if($this->getServer()->getPlayer($args[0])){
                    $sender->getInventory()->clearAll();
                    $sender->sendMessage("§aYou have cleared all items from " . IR::WHITE . $player->getName() . "§a's inventory!");
		  }else{
                     $sender->sendMessage("§cPlayer not found in server!");
		     return false;
                    }
                   return true;
		}
		if(strtolower($command->getName()) == "cleararmor") {
			if(!$sender instanceof Player){
                               $sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /cleararmor");
                               $sender->sendMessage("§8- §aClear armor from player§e: §b/cleararmor§f <player>");
                               return false;
                               }
                              if(!$sender->hasPermission("implactor.cleararmor")){       
                              $sender->sendMessage("§cYou have no permission allowed to use §aClear §ccommand§e!");
                              return false;
                             }                     
                             if(empty($args[0])){    
                             $sender->getArmorInventory()->clearAll();
                             $sender->sendMessage("§eArmor §awas cleared successfully from your body!");
                             return false;
                            }
                           $player = $this->getServer()->getPlayer($args[0]);
                           if($this->getServer()->getPlayer($args[0])){
                           $sender->getArmorInventory()->clearAll();
                           $sender->sendMessage("§aYou have cleared armor from " . IR::WHITE . $player->getName() . "");
			 }else{
                           $sender->sendMessage("§cPlayer not found in server!");
                           return false;
                         }
                        return true;
		}
		if(strtolower($command->getName()) == "clearall") {
			if(!$sender instanceof Player){
				$sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /clearall");
                                $sender->sendMessage("§8- §aClear all from player§e: §b/clearall§f <player>");
                                return false;
                                }
                            if(!$sender->hasPermission("implactor.clearall")){        
                            $sender->sendMessage("§cYou have no permission allowed to use §aClear §ccommand§e!");
                            return false;
                           }                            
                           if(empty($args[0])){
                           $sender->getInventory()->clearAll();
                           $sender->getArmorInventory()->clearAll();
                           $sender->sendMessage("§aAll of §eitems §aand §earmor §awas cleared successfully from yourself!");
                           return false;
                          }
                         $player = $this->getServer()->getPlayer($args[0]);
                         if($this->getServer()->getPlayer($args[0])){
                         $sender->getInventory()->clearAll();
                         $sender->getArmorInventory()->clearAll();
                         $sender->sendMessage("§aYou have cleared all of items and and armor from " . IR::WHITE . $player->getName() . "");
                       }else{
                         $sender->sendMessage("§cPlayer not found in server!");
                         return false;
                      }
                      return true;
	        }
		if(strtolower($command->getName()) == "feed"){
			if(!$sender instanceof Player){
                                $sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /feed");
                                $sender->sendMessage("§8- §aFeed a player§e: §b/feed§f <player>");
                                return false;
                              }
                          if(!$sender->hasPermission("implactor.feed")){
                          $sender->sendMessage("§cYou have no permission allowed to use §aFeed §ccommand§e!");
                          return false;
                        }
                     if(empty($args[0])){
                     $sender->setFood(20);
                     $sender->setSaturation(20);
                     $sender->sendMessage("§aYou have fed yourself!");
                     return false;
                   }
                   $player = $this->getServer()->getPlayer($args[0]);
                   if($this->getServer()->getPlayer($args[0])){
                   $player->setFood(20);
                   $player->setSaturation(20);
                   $sender->sendMessage(IR::GREEN . $player->getName() . "§e has been fed!");
                 }else{
                   $sender->sendMessage("§cPlayer not found in server!");
                   return false;
                  }
                  return true;
                }
		if(strtolower($command->getName()) == "ihelp") {
			if($sender instanceof Player){
				if($sender->hasPermission("implactor.command.help")){
					$sender->sendMessage("§b--( §eCoreX §aHelp §b)--");
					$sender->sendMessage("§e/ihelp §9- §fCoreX Command List!");
					$sender->sendMessage("§e/iabout §9- §fAbout CoreX plugin!");
					$sender->sendMessage("§e/ping §9- §fCheck your ping status on server!");
					$sender->sendMessage("§e/feed §9- §fFeed yourself or other players when on hunger!");
					$sender->sendMessage("§e/heal §9- §fHeal yourself or other players!");
					$sender->sendMessage("§e/gms §9- §fChange their gamemode to §cSurvival §fmode!");
					$sender->sendMessage("§e/gmc §9- §fChange their gamemode to §aCreative §fmode!");
					$sender->sendMessage("§e/gma §9- §fChange their gamemode to §cAdventure §fmode!");
					$sender->sendMessage("§e/gmspc §9- §fChange their gamemode to §bSpectator §fmode!");
					$sender->sendMessage("§e/hub §9- §fTeleport/Return To Hub!");
					$sender->sendMessage("§e/sethub §9- §fSet the main hub location point!");
					$sender->sendMessage("§e/fly §9- §fTurn on/off the fly ability!");
					$sender->sendMessage("§e/kill §9- §fBe a brave and kill yourself!");
					$sender->sendMessage("§e/wild §9- §fTeleport to the random wild zone!");
					$sender->sendMessage("§e/clearitem §9- §fClear items from their inventory!");
					$sender->sendMessage("§e/cleararmor §9- §fClear armor from their body!");
					$sender->sendMessage("§e/clearall §9- §fClear all items/armors from their inventory and body!");
					$sender->sendMessage("§e/nick §9- §fSet your nickname or default!");
					$sender->sendMessage("§e/freeze §9- §bFreeze §fyourself or others will make you frozen!");
					$sender->sendMessage("§e/vanish §9- §6Vanish §fyourself or others will make you invisible!");
					$sender->sendMessage("§e/bot §9- §fSpawn your own §cbot §fhuman!");
					$sender->sendMessage("§e/icast §9- §bBroadcast §fmessage to all players with §dCoreCast!");		     
					$sender->sendMessage("§e/ibook §9- §fGet a §bbook §fvia command!");
                                        $sender->sendMessage("§e/size §9- §fSet your player size!");
					return true;
				}
			}
		}                                           
		if(strtolower($command->getName()) == "iabout") {
			if($sender instanceof Player){
				if($sender->hasPermission("implactor.command.about")){
					$sender->sendMessage("§8---==============================---");
					$sender->sendMessage(" §bCoreX §dplugin for §eMinecraft: Bedrock");
					$sender->sendMessage("§8- §cAuthor: §fEmeraldAssasinYT , ZarkysMC");
					$sender->sendMessage("§8- §bDate: §f23 §eMay §f2018");
					$sender->sendMessage("§8- §6Latest API: §f3.0.0-ALPHA12");
					$sender->sendMessage("§8--===---");
					$sender->sendMessage("§3A plugin with advanced features");
					$sender->sendMessage("§8---==============================---");
					return true;
				}
			}
		}
		if(strtolower($command->getName()) === "vanish") {
			if(!$sender instanceof Player){
				$sender->sendMessage("Please use CoreX command in-game server!");
				return false;
			}
			if(!$sender->hasPermission("implactor.vanish")){
				$sender->sendMessage("§cYou have no permission allowed to use §bFreeze §ccommand§e!");
				return false;
			}
			if(empty($args[0])){
				if(!in_array($sender->getName(), $this->vanish)){
					$this->vanish[] = $sender->getName();
					$sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
					$sender->setNameTagVisible(false);
					$sender->sendMessage("§bYou are now §fvanished!");
				}elseif(in_array($sender->getName(), $this->vanish)){
					unset($this->vanish[array_search($sender->getName(), $this->vanish)]);
					$sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
					$sender->setNameTagVisible(true);
					$sender->sendMessage("§bYou are no longer §fvanished!");
				}
				return false;
			}
			if($this->getServer()->getPlayer($args[0])){
				$player = $this->getServer()->getPlayer($args[0]);
				if(!in_array($player->getName(), $this->vanish)){
					$this->vanish[] = $player->getName();
					$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
					$player->setNameTagVisible(false);
					$player->sendMessage("§bYou are now §fvanished!");
					$sender->sendMessage("§eYou have successfully §fvanished " . IR::GREEN . $player->getName() . "");
				}elseif(in_array($player->getName(), $this->vanish)){
					unset($this->vanish[array_search($player->getName(), $this->vanish)]);
					$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
					$player->setNameTagVisible(true);
					$player->sendMessage("§bYou are no longer §fvanished!");
					$sender->sendMessage("§eYou have successfully §fun-vanished " . IR::GRREN . $player->getName() . "");
				}
			}else{
				$sender->sendMessage("§cPlayer not found in-game server!");
				return false;
			}
			return true;
		}
		if(strtolower($command->getName()) === "freeze") {
			if(!$sender instanceof Player){
				$sender->sendMessage("§cPlease use Implactor command in-game server!");
				return false;
			}
			if(!$sender->hasPermission("implactor.freeze")){
				$sender->sendMessage("§cYou have no permission allowed to use §bFreeze §ccommand§e!");
				return false;
			}
			if(empty($args[0])){
				$sender->sendMessage("§8§l(§6!§8)§r §cCommand Usage§e:§r §b/freeze <player>");
				return false;
			}
			if($this->getServer()->getPlayer($args[0])){
				$player = $this->getServer()->getPlayer($args[0]);
				if(!in_array($player->getName(), $this->freeze)){
					$this->freeze[] = $player->getName();
					$player->sendMessage(IR::AQUA . "You are now frozen to yourself!");
					$sender->sendMessage(IR::AQUA . "You have froze " . $player->getName());
				}elseif(in_array($player->getName(), $this->freeze)){
					unset($this->freeze[array_search($player->getName(), $this->freeze)]);
					$player->sendMessage(IR::AQUA . "You are now longer be frozen!");
					$sender->sendMessage(IR::AQUA . "You have unfroze " . $player->getName());
				}
			}else{
				$sender->sendMessage("§cPlayer not found in server!");
				return false;
			}
			return true;
		}
		if(strtolower($command->getName()) == "bot") {
			if($sender instanceof Player){
				if($sender->hasPermission("implactor.bot")){
					if(count($args) < 1){
						$sender->sendMessage("§l§8(§6!§8)§r §cCommand usage§8:§r§7 /bot <name>");
						return false;
					}
					$this->spawnBot($sender, $args[0]);
					$sender->sendMessage("§eYou have summoned a §bbot §enamed§c:§r " . $args[0]);
					$sender->getServer()->broadcastMessage("§7[§bBot§7]§f ". IR::GOLD . $sender->getPlayer()->getName() . IR::WHITE ." has spawned a §bbot §fwith named §d" .$args[0]. "§f!");
				}else{
					$sender->sendMessage("§cYou have no permission allowed to use special §bBot §ccommand§e!");
					return false;
				}
				return true;
			}
		}
		if(strtolower($command->getName()) == "icast") {
			if($sender instanceof Player){
				if($sender->hasPermission("implactor.broadcast")){
					if(count($args) < 1){
						$sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /icast <message>");
						return false;
					}
					$sender->getServer()->broadcastMessage("§7[§bImplacast§7] §b" . IR::YELLOW . implode(" ", $args));
					return true;
				}
			}
		}
		if(strtolower($command->getName()) == "ibook") {
			if($sender instanceof Player){
				if($sender->hasPermission("implactor.book")){
					$this->getImplactorBook($sender);
					$sender->sendMessage("§6CubeX Lords Of Wisdom Have \n Senteth Forward You A Book, ". $sender->getPlayer()->getName(). "§f!");
                                        $sender->getLevel()->addSound(new FizzSound($sender));
					return true;
				}
			}
		}
		if(strtolower($command->getName()) == "heal") {
			if(!$sender instanceof Player){
                                $sender->sendMessage("§8(§6!§8)§r §cCommand usage§8:§r§7 /heal");
                                $sender->sendMessage("§8- §aHeal a player§e: §b/heal§f <player>");
                                return false;
                              }
                          if(!$sender->hasPermission("implactor.heal")){
                          $sender->sendMessage("§cYou have no permission allowed to use §fHeal §ccommand§e!");
                          return false;
                        }
                     if(empty($args[0])){
                     $sender->setHealth(40);
                     $sender->sendMessage(IR::GREEN . "You have been healed!");
                     return false;
                   }
                   $player = $this->getServer()->getPlayer($args[0]);
                   if($this->getServer()->getPlayer($args[0])){
                   $player->setHealth(40);
                   $sender->sendMessage(IR::GREEN . $player->getName() . " has been healed!");
                 }else{
                   $sender->sendMessage("§cPlayer not found in server!");
                   return false;
                 }
                 return true;
              }
           }			     
                 
                 // GIVE A BOOK TO PLAYER \\
	         public function getImplactorBook(Player $player): void{
		     $ibook = Item::get(Item::WRITTEN_BOOK, 0, 1);
		     $ibook->setTitle(IR::GREEN . IR::UNDERLINE . "CubeX Book Of Gods");
		     $ibook->setPageText(0, IR::GREEN . IR::UNDERLINE . "Coming Soon" . IR::BLACK . "- EmeraldAssasinYT");
		     $ibook->setPageText(1, IR::GREEN . IR::UNDERLINE . "Coming Soon" . IR::BLACK . "- ZarkysMC");
		     $ibook->setAuthor("ZarkysMC");
		     $player->getInventory()->addItem($book);
	        }
	        
                 // MOTD \\
	         public static function setMotd(string $motd) : void{  
                     $this->getServer()->getNetwork()->setName("§bCubeX §eNetwork " .$motd);  
               } 
	
               // CLEAR LAGG | CLEAR ALL ITEMS \\
	       public function clearItems(): int{
               $i = 0;
               foreach($this->getServer()->getLevels() as $level){
               foreach($level->getEntities() as $entity){
                if(!$this->isEntityExempted($entity) && !($entity instanceof Creature)){
                    $entity->close();
                    $i++;
                    }
                  }
                }
               return $i;
             }
            
              // CLEAT LAGG | CLEAR ALL MOBS & HUMANS \\
              public function clearMobs(): int{
              $i = 0;
              foreach($this->getServer()->getLevels() as $level){
              foreach($level->getEntities() as $entity){
               if(!$this->isEntityExempted($entity) && $entity instanceof Creature && !($entity instanceof Human)){
                    $entity->close();
                    $i++;
                  }
               }
             }
             return $i;
           }
           
              public function exemptEntity(Entity $entity) : void{
              $this->exemptedEntities[$entity->getID()] = $entity;
            }
            
              public function isEntityExempted(Entity $entity) : bool{
              return isset($this->exemptedEntities[$entity->getID()]);
            }
         }
