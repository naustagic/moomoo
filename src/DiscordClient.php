<?php

namespace RPurinton\Moomoo;

require_once(__DIR__."/ConfigLoader.php");

use React\EventLoop\Factory;
use Discord\Discord;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;

class DiscordClient Extends ConfigLoader
{

	private $loop = null;
	private $discord = null;
	private $commands = array();

	function __construct()
	{
		parent::__construct();
		$this->commands[] = "!ign ";
                $this->commands[] = "!apply ";
                $this->commands[] = "! ign ";
                $this->commands[] = "! apply ";
                $this->commands[] = "!ing ";
                $this->commands[] = "ign ";
                $this->commands[] = "ing ";
                $this->commands[] = "apply ";
		$this->loop = Factory::create();
		$this->config["discord"]["loop"] = $this->loop;
		$this->config["discord"]["intents"] = Intents::getDefaultIntents() | Intents::GUILD_MEMBERS;
		$this->discord = new Discord($this->config["discord"]);
		$this->discord->on(Event::MESSAGE_CREATE, $this->MESSAGE_CREATE(...));
                $this->discord->run();
        }

	private function parse($content,$command)
        {
                $len = strlen($command);
                $cmd = strtolower($command);
                $sub = strtolower(substr($content,0,$len));
                $val = substr($content,$len+1);
                if($cmd == $sub) return $val;
                return false;
        }

        private function MESSAGE_CREATE($message,$discord)
        {
                if(mb_strlen($message->content) < 6) return true;
                foreach($this->commands as $cmd)
                {
                        $val = $this->parse($message->content,$cmd);
                        if($val) return $this->ign($message,$val);
                }
        }

	protected function ign($message,$query)
	{
		$query = urlencode(substr($message->content,strpos($message->content," ")+1));
		$result = json_decode(file_get_contents("https://api.mir4.gq/v1/search/$query"),true);
		if(isset($result["error"]))
		{
			if($result["error"] == "no response") return $message->reply("404 not found");
			return $message->reply($result["error"]);
		}
		$packet = $result["characters"][0]["data"];
		if($message->guild_id = "1012270279386480650" && $message->channel->id == "1012270279956905995")
		{
			$message->member->removeRole("1022491714075762779");
			$message->member->setNickname($result["characters"][0]["name"]);
			if($packet["server"]["id"] != "115")
			{
				$message->member->addRole("1022100654707200082");
			}
			else
			{
				$message->member->addRole("1022060560919056434");
				switch($packet["clan"]["name"])
				{
					case "DTM Sicarios":
					case "DTM 大精灵 I":
						$message->member->addRole("1022048247616913448");
						$message->member->addRole("1022048828595109939");
						break;
					case "DTM 大精灵 II":
						$message->member->addRole("1022048527985160223");
						$message->member->addRole("1022048828595109939");
						break;
					case "DTM 大精灵 III":
						$message->member->addRole("1022048345281277973");
						$message->member->addRole("1022048828595109939");
						break;
					case "DTM 大精灵 IV":
						$message->member->addRole("1022048673011609682");
						$message->member->addRole("1022048828595109939");
						break;
					case "DTM 大精灵 V":
						$message->member->addRole("1022048568686690344");
						$message->member->addRole("1022048828595109939");
						break;
					case "DTM 大精灵 VI":
						$message->member->addRole("1022786653464559636");
						$message->member->addRole("1022048828595109939");
						break;
				}
			}
		}


		if($packet["power"] > 0) $color = "#28674F";
		if($packet["power"] > 135000) $color = "#20416b";
		if($packet["power"] > 170000) $color = "#751d20";
		if($packet["power"] > 205000) $color = "#b2931b";
		$embed = new \Discord\Parts\Embed\Embed($this->discord);
		$embed->setColor($color);
		$embed->setTitle($result["characters"][0]["name"]);
		$description = $packet["class"]["name"]." ".number_format($packet["power"],0,".",",")." PS\n";
		$description .= "Clan: ".$packet["clan"]["name"]."\n";
		$description .= "Clan Rank: ".$packet["clan"]["rank"]."\n";
		$description .= "Server: ".$packet["server"]["name"]."\n";
		$description .= "Server Rank: ".$packet["server"]["rank"]."\n";
		$description .= "Region: ".$packet["region"]["name"]."\n";
		$description .= "Region Rank: ".$packet["region"]["rank"]."\n";
		$description .= "Global Rank: ".$packet["global_rank"]."\n";
		$embed->setDescription($description);
		$message->reply(\Discord\Builders\MessageBuilder::new()
				->addEmbed($embed));
	}

}
