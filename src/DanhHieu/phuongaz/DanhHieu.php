<?php

/** --= Danh Hiệu =--
* Designer & Editor: BlackPMFury
* New Version: 2.0 UI
* What's New with Ver 2.0: Added Form To Plugin.
*/

namespace DanhHieu\phuongaz;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\{ CommandSender, Command, ConsoleCommandSender};
use pocketmine\event\player\{PlayerJoinEvent, PlayerInteractEvent};
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat as TF;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI;

class DanhHieu extends PluginBase implements Listener{

    var $formapi;
    var $config;
    var $user;
    const PREFIX = "§l§c❤§d DANH HIỆU §c❤";

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->config = new Config($this->getDataFolder() . "danhhieus.yml", Config::YAML);
        $this->user = new Config($this->getDataFolder() . "users.yml", Config::YAML);
        $this->formapi = $this->getServer()->getPluginManager()->getPlugin('FormAPI');
    }
    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $danhhieu = $this->getDanhHieu($player);
        if($danhhieu == ''){
            return true;
        }
        $format = $this->getFormat($danhhieu);
        $player->setDisplayName($format.' '. $player->getName());
    }

    public function TaoDanhHieu(string $new){
       if($this->KiemTraDanhHieu($new) == false ){
           $this->config->set($new, ["format" => "[".$new."]", "nametag" => "[".$new."]"]);
           $this->config->save();
       }

    }
    public function KiemTraDanhHieu(string $danhhieu){
        $all = $this->config->getAll();
        if (isset($all[$danhhieu])){
            return true;
        }
        return false;
    }

    public function getAll(){
        return $this->config->getAll();
    }


    public function getFormat(string $danhhieu){
        return $this->config->get($danhhieu)["format"];
    }

    public function getNameTag(string $danhhieu){

        return $this->config->get($danhhieu)["nametag"];
    }

    public function updateFormat(string $danhhieu,string $new){
    if($this->KiemTraDanhHieu($danhhieu) == true ){
        $this->config->set($danhhieu, ["format" => $new, "nametag" => $this->getNameTag($danhhieu)]);
            $this->config->save();
        }
    }

    public function updateNameTag(string $danhhieu,string $new){
        if($this->KiemTraDanhHieu($danhhieu) == true ){
            $this->config->set($danhhieu, ["format" => $this->getFormat($danhhieu), 'nametag' => $new]);
            $this->config->save();
        }
    }

    public function getPermission($danhhieu){
        if($this->KiemTraDanhHieu($danhhieu) == true){
            $dh = strtolower($danhhieu);
            $perm = 'danhhieu.'.$dh;
            return $perm;
        }
    }

    public function setDanhHieu($player, $danhhieu){
        if($this->KiemTraDanhHieu($danhhieu) == true){
            $this->user->set(strtolower($player->getName()), $danhhieu);
            $this->user->save();
            $player->setDisplayName($this->getFormat($danhhieu). ' '. $player->getName());
        }

    }

    public function getDanhHieu($player){
        $all = $this->user->getAll();
        $name = strtolower($player->getName());
        if(isset($all[$name])){
            $danhhieu = $this->user->get($name);
            return $danhhieu;
        }
        return "";
    }
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		switch(strtolower($cmd->getName())){
			case "danhhieu":
			if(!($sender instanceof Player)){
				$this->getServer()->getLogger()->warnings("Please use in-game!");
				return true;
			}
			$a = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			$f = $a->createSimpleForm(Function (Player $p, $data){
				
				$result = $data;
				if($result == null){
				}
				switch($result){
					case 0:
					$this->createFormat($p);
					break;
					case 1:
					$this->setFormat($p);
					break;
					case 2:
					$this->setNameT($p);
					break;
					case 3:
					$this->listNameT($p);
					break;
					case 4:
					$this->useFormat($p);
					break;
					case 5:
					$msg = "§a-=== §cDanh Hiệu §a===-\n* Coder: PhuongAz\nEditor: BlackPMFury";
					break;
				}
			});
			$f->setTitle("§l§c❤§d DANH HIỆU §c❤");
			$f->setContent("§cJust Try Hard =]]]");
			$f->addButton("Create", 0);
			$f->addButton("setFormat", 1);
			$f->addButton("setNameTag", 2);
			$f->addButton("List", 3);
			$f->addButton("Su Dung", 4);
			$f->addButton("Version", 5);
			$f->sendToPlayer($sender);
		}
		return true;
	}
	
	public function createFormat($p){
	    $a = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	    $f = $a->createCustomForm(Function (Player $p, $data){
			if(isset($data[1])){
				if ($this->KiemTraDanhHieu($data[1]) == false ){
					$this->TaoDanhHieu($data[1]);
					$sender->sendMessage(self::PREFIX.TF::GREEN." Đã tạo danh hiệu: ". $data[1]);
				}
			}
		    $p->sendMessage("§aNice! So cool!");
		});
		$f->setTitle(self::PREFIX);
	    $f->addContent("§aYou can Write Your Nametsg In here And Whatever Before Kicked you!\n===================");
	    $f->addInput("§Create Here Bois!");
		$f->sendToPlayer($p);
	}
	
	public function setFormat($p){
		$a = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	    $f = $a->createCustomForm(Function (Player $p, $data){
			if(isset($data[1])){
				if($this->KiemTraDanhHieu($data[1]) == true){
					$new = implode(" ", array_slice($data, 2));
                    $this->updateFormat($data[1], $new);
                    $sender->sendMessage(self::PREFIX.TF::GREEN." Bạn đã setformat ".TF::YELLOW.$new.TF::GREEN." cho ".TF::YELLOW.$data[1]);
				}
			}
			$p->sendMessage("§aNice! So cool!");
		});
		$f->setTitle(self::PREFIX);
	    $f->addContent("§aYou can Write Your Nametsg In here And Whatever Before Kicked you!\n===================");
	    $f->addInput("§Create Here Bois!");
		$f->sendToPlayer($p);
	}
	
	public function setNameT($p){
		$a = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	    $f = $a->createCustomForm(Function (Player $p, $data){
			if(isset($data[1])){
                if($this->KiemTraDanhHieu($data[1]) == true ){
                    $new = implode(" ", array_slice($args, 2));
                    $this->updateNameTag($data[1], $new);
                    $sender->sendMessage(self::PREFIX.TF::GREEN." Bạn đã nametag ".TF::YELLOW.$new.TF::GREEN." cho ".TF::YELLOW.$data[1]);
				}
			}
			$p->sendMessage("§aNice! So cool!");
		});
		$f->setTitle(self::PREFIX);
	    $f->addContent("§aYou can Write Your Nametsg In here And Whatever Before Kicked you!\n===================");
	    $f->addInput("§Create Here Bois!");
		$f->sendToPlayer($p);
	}
	
	public function listNameT($p){
		$all = $this->getAll();
        $form = new CustomForm(function(Player $p, $data){
		});
        $form->setTitle(self::PREFIX);
        if($all != ''){
            $form->addLabel(TF::YELLOW."-=  Các danh hiệu  =-");

            foreach(array_keys($all) as $danhhieus){
                $perm = $this->getPermission($danhhieus);
                if($p->hasPermission($perm)){
                    $form->addLabel(TF::YELLOW." ". $danhhieus .TF::GREEN. " ( bạn đã sỡ hữu )");
				}else{
					$form->addLabel(TF::GREEN." ". $danhhieus);
				}
			}
            $form->sendToPlayer($p);
		}
	}
	
	public function useFormat($p){
		$a = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	    $f = $a->createCustomForm(Function (Player $p, $data){
			if(isset($data[1])){
                $perm = $this->getPermission($args[1]);
                if($p->hasPermission($perm)){
                    $this->setDanhHieu($sender, $data[1]);
                    $sender->sendMessage(self::PREFIX.TF::GREEN." Đã sử dụng danh hiệu: ". $data[1]);
                }
                return true;
			}
            $sender->sendMessage(self::PREFIX.TF::YELLOW." /danhhieu sudung (danh hiệu bạn muốn sử dụng)");
		});
		$f->setTitle(self::PREFIX);
	    $f->addContent("§aYou can Write Your Nametsg In here And Whatever Before Kicked you!\n===================");
	    $f->addInput("§Create Here Bois!");
		$f->sendToPlayer($p);
	}
}