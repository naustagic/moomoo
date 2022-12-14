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
	private $apply_commands = array();
	private $move_commands = array();

	function __construct()
	{
		parent::__construct();
		$this->apply_commands[] = "!ign ";
		$this->apply_commands[] = "!ign  ";
                $this->apply_commands[] = "!apply ";
                $this->apply_commands[] = "!apply  ";
                $this->apply_commands[] = "! ign ";
                $this->apply_commands[] = "! ign  ";
                $this->apply_commands[] = "! apply ";
                $this->apply_commands[] = "! apply  ";
                $this->apply_commands[] = "!ing  ";
                $this->apply_commands[] = "ign ";
                $this->apply_commands[] = "ign  ";
                $this->apply_commands[] = "ing ";
                $this->apply_commands[] = "ing  ";
                $this->apply_commands[] = "apply ";
                $this->apply_commands[] = "apply  ";
                $this->move_commands["!move"] = 1;
                $this->move_commands["!move "] = 1;
                $this->move_commands["!move  "] = 1;
                $this->move_commands["move"] = 1;
                $this->move_commands["move "] = 1;
                $this->move_commands["move  "] = 1;
                $this->move_commands[" !move"] = 1;
                $this->move_commands[" !move "] = 1;
                $this->move_commands[" !move  "] = 1;
                $this->move_commands[" move"] = 1;
                $this->move_commands[" move "] = 1;
                $this->move_commands[" move  "] = 1;
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
                if(mb_strlen($message->content) < 5) return true;
                foreach($this->apply_commands as $cmd)
                {
                        $val = $this->parse($message->content,$cmd);
                        if($val) return $this->ign($message,$val);
                }
		if(isset($this->move_commands[$message->content])) return $this->move($message);
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
			$message->member->setNickname($result["characters"][0]["name"])->done(function () use ($message,$result,$packet)
			{
				if($packet["server"]["id"] != "115")
				{
					$message->member->addRole("1022100654707200082");
				}
				else
				{
					$message->member->addRole("1022060560919056434")->done(function () use ($message,$packet)
					{
						switch($packet["clan"]["name"])
						{
							case "DTM Sicarios":
							case "DTM ????????? I":
								$message->member->addRole("1022048247616913448")->done(function () use ($message)
								{
									$message->member->addRole("1022048828595109939");
								});
								break;
							case "DTM ????????? II":
								$message->member->addRole("1022048527985160223")->done(function () use ($message)
								{
									$message->member->addRole("1022048828595109939");
								});
								break;
							case "DTM ????????? III":
								$message->member->addRole("1022048345281277973")->done(function () use ($message)
								{
									$message->member->addRole("1022048828595109939");
								});
								break;
							case "DTM ????????? IV":
								$message->member->addRole("1022048673011609682")->done(function () use ($message)
								{
									$message->member->addRole("1022048828595109939");
								});
								break;
							case "DTM ????????? V":
								$message->member->addRole("1022048568686690344")->done(function () use ($message)
								{
									$message->member->addRole("1022048828595109939");
								});
								break;
							case "DTM ????????? VI":
								$message->member->addRole("1022786653464559636")->done(function () use ($message)
								{
									$message->member->addRole("1022048828595109939");
								});
								break;
							case "DTM ????????? VII":
								$message->member->addRole("1024495866419105893")->done(function () use ($message)
								{
									$message->member->addRole("1022048828595109939");
								});
								break;
						}
					});
				}
			});
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
	protected function move($message)
	{
		$query = urlencode($message->member->nick);
		$result = json_decode(file_get_contents("https://api.mir4.gq/v1/search/$query"),true);
		if(isset($result["error"]))
		{
			if($result["error"] == "no response") return $message->reply("404 not found");
			return $message->reply($result["error"]);
		}
		$packet = $result["characters"][0]["data"];

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
				->setContent("Thank You, <@{$message->member->id}>, you will be considered the next time we reorganize the clans!")
				->addEmbed($embed));
	}

}
