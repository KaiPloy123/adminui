<?php

namespace AdminMenu;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\player\GameMode; // อิมพอร์ต GameMode
use pocketmine\event\Listener;
use Vecnavium\FormsUI\SimpleForm;
use Vecnavium\FormsUI\CustomForm;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener {
    
    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if($command->getName() === "am") {
            if(!$sender instanceof Player) {
                $sender->sendMessage(TF::RED . "คำสั่งนี้ใช้ได้เฉพาะในเกมเท่านั้น");
                return true;
            }

            if(!$sender->hasPermission("adminmenu.use")) {
                $sender->sendMessage(TF::RED . "คุณไม่มีสิทธิ์ใช้คำสั่งนี้");
                return true;
            }

            $this->openMainMenu($sender);
            return true;
        }
        return false;
    }

    public function openMainMenu(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data) {
            if($data === null) return;
            
            switch($data) {
                case 0:
                    $this->openTimeMenu($player);
                    break;
                case 1:
                    $player->setHealth($player->getMaxHealth());
                    $player->getHungerManager()->setFood(20);
                    $player->sendMessage(TF::GREEN . "รักษาและเติมความหิวแล้ว");
                    break;
                case 2:
                    $this->openGamemodeMenu($player);
                    break;
                case 3:
                    $this->toggleFly($player);
                    break;
                case 4:
                    $this->openKickForm($player);
                    break;
                case 5:
                    $this->openBanForm($player);
                    break;
            }
        });

        $form->setTitle("§l§6เมนูแอดมิน");
        $form->setContent("เลือกคำสั่งที่ต้องการใช้งาน:");
        $form->addButton("§eจัดการเวลา\n§7คลิกเพื่อเปลี่ยนเวลา", 0, "textures/items/clock");
        $form->addButton("§aรักษา + เติมความหิว\n§7คลิกเพื่อรักษา", 0, "textures/items/apple_golden");
        $form->addButton("§6เปลี่ยนโหมดเกม\n§7คลิกเพื่อเปลี่ยนโหมด", 0, "textures/items/diamond_pickaxe");
        $form->addButton($player->getAllowFlight() ? "§bปิดการบิน\n§7คลิกเพื่อปิด" : "§bเปิดการบิน\n§7คลิกเพื่อเปิด", 0, "textures/items/feather");
        $form->addButton("§cเตะผู้เล่น\n§7คลิกเพื่อเตะ", 0, "textures/items/door_iron");
        $form->addButton("§4แบนผู้เล่น\n§7คลิกเพื่อแบน", 0, "textures/blocks/tnt_side");
        
        $player->sendForm($form);
    }

    public function toggleFly(Player $player): void {
        if($player->getAllowFlight()) {
            $player->setAllowFlight(false);
            $player->setFlying(false);
            $player->sendMessage(TF::GREEN . "ปิดการบินแล้ว");
        } else {
            $player->setAllowFlight(true);
            $player->sendMessage(TF::GREEN . "เปิดการบินแล้ว กดกระโดด 2 ครั้งเพื่อบิน");
        }
        $this->openMainMenu($player);
    }

    public function openTimeMenu(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data) {
            if($data === null) {
                $this->openMainMenu($player);
                return;
            }
            
            switch($data) {
                case 0:
                    $player->getWorld()->setTime(0);
                    $player->sendMessage(TF::GREEN . "ตั้งเวลาเป็นกลางวันแล้ว");
                    break;
                case 1:
                    $player->getWorld()->setTime(14000);
                    $player->sendMessage(TF::GREEN . "ตั้งเวลาเป็นกลางคืนแล้ว");
                    break;
            }
        });

        $form->setTitle("§l§eจัดการเวลา");
        $form->setContent("เลือกเวลาที่ต้องการ:");
        $form->addButton("§eตั้งเวลากลางวัน\n§7คลิกเพื่อเปลี่ยน");
        $form->addButton("§8ตั้งเวลากลางคืน\n§7คลิกเพื่อเปลี่ยน");
        
        $player->sendForm($form);
    }

    public function openGamemodeMenu(Player $player): void {
        $form = new SimpleForm(function(Player $player, ?int $data) {
            if($data === null) {
                $this->openMainMenu($player);
                return;
            }
            
            switch($data) {
                case 0:
                    $player->setGamemode(GameMode::SURVIVAL());
                    $player->sendMessage(TF::GREEN . "เปลี่ยนเป็นโหมดเอาชีวิตรอดแล้ว");
                    break;
                case 1:
                    $player->setGamemode(GameMode::CREATIVE());
                    $player->sendMessage(TF::GREEN . "เปลี่ยนเป็นโหมดสร้างแล้ว");
                    break;
                case 2:
                    $player->setGamemode(GameMode::ADVENTURE());
                    $player->sendMessage(TF::GREEN . "เปลี่ยนเป็นโหมดผจญภัยแล้ว");
                    break;
                case 3:
                    $player->setGamemode(GameMode::SPECTATOR());
                    $player->sendMessage(TF::GREEN . "เปลี่ยนเป็นโหมดสเปกเตเตอร์แล้ว");
                    break;
            }
        });

        $form->setTitle("§l§6เปลี่ยนโหมดเกม");
        $form->setContent("เลือกโหมดที่ต้องการ:");
        $form->addButton("§aโหมดเอาชีวิตรอด\n§7คลิกเพื่อเปลี่ยน");
        $form->addButton("§6โหมดสร้าง\n§7คลิกเพื่อเปลี่ยน");
        $form->addButton("§eโหมดผจญภัย\n§7คลิกเพื่อเปลี่ยน");
        $form->addButton("§7โหมดสเปกเตเตอร์\n§7คลิกเพื่อเปลี่ยน");
        
        $player->sendForm($form);
    }

    public function openKickForm(Player $player): void {
        $form = new CustomForm(function(Player $player, ?array $data) {
            if($data === null) {
                $this->openMainMenu($player);
                return;
            }

            $target = $this->getServer()->getPlayerByPrefix($data[0]);
            if($target === null) {
                $player->sendMessage(TF::RED . "ไม่พบผู้เล่นที่ระบุ");
                return;
            }

            $reason = $data[1] !== "" ? $data[1] : "ถูกเตะโดยแอดมิน";
            $target->kick($reason);
            $player->sendMessage(TF::GREEN . "เตะผู้เล่น " . $target->getName() . " แล้ว");
        });

        $form->setTitle("§l§cเตะผู้เล่น");
        $form->addInput("ชื่อผู้เล่น", "Steve");
        $form->addInput("เหตุผล (ไม่จำเป็นต้องระบุ)", "ถูกเตะโดยแอดมิน");
        
        $player->sendForm($form);
    }

    public function openBanForm(Player $player): void {
        $form = new CustomForm(function(Player $player, ?array $data) {
            if($data === null) {
                $this->openMainMenu($player);
                return;
            }

            $target = $data[0];
            if($target === "") {
                $player->sendMessage(TF::RED . "กรุณาระบุชื่อผู้เล่น");
                return;
            }

            $reason = $data[1] !== "" ? $data[1] : "ถูกแบนโดยแอดมิน";
            $this->getServer()->getNameBans()->addBan($target, $reason, null, $player->getName());
            
            $targetPlayer = $this->getServer()->getPlayerByPrefix($target);
            if($targetPlayer !== null) {
                $targetPlayer->kick("คุณถูกแบนขอหา: " . $reason);
            }
            
            $player->sendMessage(TF::GREEN . "แบนผู้เล่น " . $target . " แล้ว");
        });

        $form->setTitle("§l§4แบนผู้เล่น");
        $form->addInput("ชื่อผู้เล่น", "Steve");
        $form->addInput("เหตุผล (ไม่จำเป็นต้องระบุ)", "ถูกแบนโดยแอดมิน");
        
        $player->sendForm($form);
    }
}
