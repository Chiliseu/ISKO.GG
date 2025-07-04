<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameFactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('game_facts')->insert([
            ['fact' => "Pac-Man was originally called 'Puck Man'.", 'image_path' => 'images/facts/trailblazerfacts1.png'],
            ['fact' => "Super Mario 64 pioneered full 3D movement.", 'image_path' => 'images/facts/sampofacts.png'],
            ['fact' => "Minecraft was created in just 6 days.", 'image_path' => 'images/facts/yanqingfacts2.png'],
            ['fact' => "The PlayStation 2 is the best-selling console of all time.", 'image_path' => 'images/facts/marchfacts3.png'],
            ['fact' => "Tetris was invented by a Soviet software engineer.", 'image_path' => 'images/facts/marchfacts2.png'],
            ['fact' => "The longest gaming session recorded lasted over 35 hours.", 'image_path' => 'images/facts/sushangfacts.png'],
            ['fact' => "Halo was originally developed for Mac.", 'image_path' => 'images/facts/acheronfacts.png'],
            ['fact' => "The first video game was created in 1958: a tennis simulation.", 'image_path' => 'images/facts/topazfacts.png'],
            ['fact' => "Game Boy was the first handheld with swappable cartridges.", 'image_path' => 'images/facts/jiaqouifacts.png'],
            ['fact' => "Easter eggs in games started with 'Adventure' on Atari 2600.", 'image_path' => 'images/facts/mozefacts.png'],
            ['fact' => "In Portal, the cake really was a lie.", 'image_path' => 'images/facts/aventurinefacts.png'],
            ['fact' => "Mario was originally known as Jumpman.", 'image_path' => 'images/facts/fireflyfacts.png'],
            ['fact' => "Final Fantasy was named so because it was a last-ditch effort.", 'image_path' => 'images/facts/fireflyfacts2.png'],
            ['fact' => "Street Fighter II popularized competitive fighting games.", 'image_path' => 'images/facts/marchfacts.png'],
            ['fact' => "Pikachu's name is based on Japanese onomatopoeia.", 'image_path' => 'images/facts/aventurinefacts2.png'],
            ['fact' => "Link was left-handed in early Zelda games.", 'image_path' => 'images/facts/castoricefacts.png'],
            ['fact' => "The SEGA Dreamcast was ahead of its time with online play.", 'image_path' => 'images/facts/feixaofacts2.png'],
            ['fact' => "Sonic was created to rival Nintendo’s Mario.", 'image_path' => 'images/facts/boothillfacts.png'],
            ['fact' => "The voice of Mario is Charles Martinet.", 'image_path' => 'images/facts/aventurinefacts3.png'],
            ['fact' => "Tomb Raider's Lara Croft was almost named Laura Cruz.", 'image_path' => 'images/facts/marchfacts4.png'],
            ['fact' => "StarCraft is a national sport in South Korea.", 'image_path' => 'images/facts/feixaofacts.png'],
['fact' => "Assassin’s Creed was originally a Prince of Persia spinoff.", 'image_path' => 'images/facts/feixaofacts2.png'],
['fact' => "Minecraft has sold over 300 million copies worldwide.", 'image_path' => 'images/facts/sushangfacts.png'],
['fact' => "Silent Hill’s fog was originally used to mask rendering limitations.", 'image_path' => 'images/facts/lingshafacts.png'],
['fact' => "Among Us became popular two years after release due to Twitch.", 'image_path' => 'images/facts/sparklefacts.png'],
['fact' => "Halo 3 generated $170 million on its first day.", 'image_path' => 'images/facts/marchfacts3.png'],
['fact' => "Final Fantasy VII popularized the RPG genre in the West.", 'image_path' => 'images/facts/jiaqouifacts2.png'],
['fact' => "Crash Bandicoot was designed to compete with Mario and Sonic.", 'image_path' => 'images/facts/yanqingfacts2.png'],
['fact' => "Bloodborne’s Lovecraftian horror was inspired by cosmic dread.", 'image_path' => 'images/facts/acheronfacts2.png'],
['fact' => "Street Fighter’s Hadouken means “Wave Motion Fist”.", 'image_path' => 'images/facts/blackswanfacts2.png'],
['fact' => "Mega Man was originally named “Rockman” in Japan.", 'image_path' => 'images/facts/castoricefacts.png'],
['fact' => "Banjo-Kazooie was developed in just over a year.", 'image_path' => 'images/facts/blackSwanfacts.png'],
['fact' => "Bayonetta’s combat system is based on Devil May Cry.", 'image_path' => 'images/facts/robinfacts2.png'],
['fact' => "Tetris is the first game played in space.", 'image_path' => 'images/facts/aventurinefacts.png'],
['fact' => "Bioshock’s “Would You Kindly” twist is iconic in game storytelling.", 'image_path' => 'images/facts/servalfacts.png'],
['fact' => "Dead Space used minimal UI to increase immersion.", 'image_path' => 'images/facts/jiaqouifacts.png'],
['fact' => "Guitar Hero helped revive interest in classic rock.", 'image_path' => 'images/facts/fireflyfacts.png'],
['fact' => "Plants vs. Zombies was made by just a few developers.", 'image_path' => 'images/facts/trailblazerfacts.png'],
['fact' => "Cuphead took over 5 years to animate frame-by-frame.", 'image_path' => 'images/facts/yanqingfacts.png'],
['fact' => "Hollow Knight was crowdfunded on Kickstarter.", 'image_path' => 'images/facts/lingshafacts2.png'],
['fact' => "Super Smash Bros. was originally called “Dragon King”.", 'image_path' => 'images/facts/jiaqouifacts2.png'],
['fact' => "Katamari Damacy has a cult following due to its quirky design.", 'image_path' => 'images/facts/topazfacts.png'],
['fact' => "The Legend of Zelda: Majora’s Mask was made in just one year.", 'image_path' => 'images/facts/drratiofacts.png'],
['fact' => "Mirror’s Edge was among the first to do first-person parkour.", 'image_path' => 'images/facts/sundayfacts.png'],
['fact' => "Destiny uses a “shared world shooter” model.", 'image_path' => 'images/facts/fireflyfacts2.png'],
['fact' => "Borderlands coined the term “looter shooter”.", 'image_path' => 'images/facts/yanqingfacts2.png'],
['fact' => "LittleBigPlanet emphasized player-created content.", 'image_path' => 'images/facts/marchfacts4.png'],
['fact' => "Katana Zero blends narrative with stylish combat.", 'image_path' => 'images/facts/marchfacts2.png'],
['fact' => "Death Stranding blurs the line between film and game.", 'image_path' => 'images/facts/jingyuanfacts.png'],
['fact' => "NieR: Automata’s story unfolds over multiple playthroughs.", 'image_path' => 'images/facts/sampofacts.png'],
['fact' => "Shovel Knight brought retro pixel games back into the spotlight.", 'image_path' => 'images/facts/feixaofacts.png'],
['fact' => "No Man’s Sky features a procedurally generated universe.", 'image_path' => 'images/facts/marchfacts.png'],
['fact' => "Danganronpa blends courtroom drama with anime storytelling.", 'image_path' => 'images/facts/blackswanfacts.png'],
['fact' => "Terraria is often called “2D Minecraft.”", 'image_path' => 'images/facts/sushangfacts.png'],
['fact' => "Persona 4’s TV world is inspired by fear of public perception.", 'image_path' => 'images/facts/yanqingfacts.png'],
['fact' => "Kirby was originally white, not pink.", 'image_path' => 'images/facts/sparklefacts.png'],
['fact' => "Fire Emblem popularized permadeath in strategy games.", 'image_path' => 'images/facts/jiaqouifacts.png'],
['fact' => "Genshin Impact was inspired by Breath of the Wild.", 'image_path' => 'images/facts/aventurinefacts2.png'],
['fact' => "Slay the Spire combines roguelike and card mechanics.", 'image_path' => 'images/facts/trailblazerfacts1.png'],
['fact' => "The Witcher series is based on Polish fantasy novels.", 'image_path' => 'images/facts/acheronfacts2.png'],
['fact' => "Red Dead Redemption 2 has over 500,000 lines of dialogue.", 'image_path' => 'images/facts/boothillfacts.png'],
['fact' => "Fallout was inspired by 1988’s Wasteland.", 'image_path' => 'images/facts/feixaofacts2.png'],
['fact' => "Cyberpunk 2077’s Night City was built vertically to feel immersive.", 'image_path' => 'images/facts/topazfacts.png'],
['fact' => "The Sims was originally pitched as an architecture simulator.", 'image_path' => 'images/facts/marchfacts2.png'],
['fact' => "Half-Life revolutionized storytelling in first-person shooters.", 'image_path' => 'images/facts/marchfacts3.png'],
['fact' => "L.A. Noire used motion capture tech ahead of its time.", 'image_path' => 'images/facts/jiaqouifacts.png'],
['fact' => "Mass Effect lets you shape story choices over a trilogy.", 'image_path' => 'images/facts/sundayfacts.png'],
['fact' => "Fortnite began as a co-op survival game.", 'image_path' => 'images/facts/fireflyfacts2.png'],
['fact' => "League of Legends has over 160 playable champions.", 'image_path' => 'images/facts/yanqingfacts2.png'],
['fact' => "Valorant was originally codenamed “Project A”.", 'image_path' => 'images/facts/aventurinefacts3.png'],
['fact' => "Apex Legends was surprise launched in 2019.", 'image_path' => 'images/facts/servalfacts.png'],
['fact' => "PUBG helped define the battle royale genre.", 'image_path' => 'images/facts/fireflyfacts.png'],
['fact' => "Warframe is developed by a small Canadian studio.", 'image_path' => 'images/facts/feixaofacts.png'],
['fact' => "Destiny 2 features live storytelling in seasonal arcs.", 'image_path' => 'images/facts/drratiofacts.png'],
['fact' => "Counter-Strike began as a Half-Life mod.", 'image_path' => 'images/facts/blackswanfacts2.png'],
['fact' => "Hades was the first video game to win a Hugo Award.", 'image_path' => 'images/facts/robinfacts2.png'],
['fact' => "Hearthstone was created by a small team within Blizzard.", 'image_path' => 'images/facts/blackswanfacts.png'],
['fact' => "World of Warcraft launched in 2004 and still runs today.", 'image_path' => 'images/facts/marchfacts4.png'],
['fact' => "Dota 2’s prize pool exceeds $40 million in some years.", 'image_path' => 'images/facts/jiaqouifacts2.png'],
['fact' => "Super Metroid popularized the Metroidvania genre.", 'image_path' => 'images/facts/lingshafacts2.png'],
['fact' => "Celeste was developed in just four days during a game jam.", 'image_path' => 'images/facts/sparklefacts.png'],
['fact' => "Stardew Valley was made by a solo developer in 4 years.", 'image_path' => 'images/facts/yanqingfacts.png'],
['fact' => "Journey emphasizes emotion with no words.", 'image_path' => 'images/facts/sushangfacts.png'],
['fact' => "Inside is known for its ambiguous and chilling ending.", 'image_path' => 'images/facts/mozefacts.png'],
['fact' => "Undertale can be completed without killing anyone.", 'image_path' => 'images/facts/boothillfacts.png'],
['fact' => "Braid helped launch the indie game boom.", 'image_path' => 'images/facts/marchfacts.png'],
['fact' => "Hollow Knight’s lore is told entirely through exploration.", 'image_path' => 'images/facts/feixaofacts2.png'],
['fact' => "Disco Elysium features no traditional combat.", 'image_path' => 'images/facts/fireflyfacts.png'],
['fact' => "Outer Wilds runs on a persistent 22-minute time loop.", 'image_path' => 'images/facts/yanqingfacts2.png'],
['fact' => "Oxenfree uses natural speech interruptions.", 'image_path' => 'images/facts/aventurinefacts2.png'],
['fact' => "Papers, Please simulates border security ethics.", 'image_path' => 'images/facts/topazfacts.png'],
['fact' => "Limbo’s black-and-white world evokes dread and curiosity.", 'image_path' => 'images/facts/lingshafacts.png'],
['fact' => "Dead Cells combines roguelike and Metroidvania elements.", 'image_path' => 'images/facts/jiaqouifacts.png'],
['fact' => "Return of the Obra Dinn uses 1-bit art style for impact.", 'image_path' => 'images/facts/aventurinefacts.png'],
['fact' => "Ghost of Tsushima has a Kurosawa Mode for classic film vibe.", 'image_path' => 'images/facts/castoricefacts.png'],
['fact' => "It Takes Two is a co-op-only platformer about divorce.", 'image_path' => 'images/facts/jiaqouifacts2.png'],
['fact' => "A Way Out pioneered split-screen narrative gameplay.", 'image_path' => 'images/facts/trailblazerfacts1.png'],
['fact' => "Splatoon was Nintendo’s first new IP in a decade.", 'image_path' => 'images/facts/marchfacts.png'],
['fact' => "ARMS was developed by the Mario Kart team.", 'image_path' => 'images/facts/aventurinefacts3.png'],
['fact' => "Bayonetta 3 lets you control giant demons mid-battle.", 'image_path' => 'images/facts/robinfacts.png'],
['fact' => "Hi-Fi Rush dropped as a shadow release by Tango Gameworks.", 'image_path' => 'images/facts/yanqingfacts2.png'],
['fact' => "Neon White mixes speedrunning and FPS gameplay.", 'image_path' => 'images/facts/sampofacts.png'],
['fact' => "Signalis is inspired by classic survival horror.", 'image_path' => 'images/facts/fireflyfacts2.png'],
['fact' => "Sea of Stars is a love letter to SNES-era RPGs.", 'image_path' => 'images/facts/feixaofacts.png'],
['fact' => "Pizza Tower is a spiritual successor to Wario Land.", 'image_path' => 'images/facts/acheronfacts2.png'],
['fact' => "Viewfinder lets you walk into your own photos.", 'image_path' => 'images/facts/marchfacts3.png'],
['fact' => "Balatro mixes poker with roguelike chaos.", 'image_path' => 'images/facts/sundayfacts.png'],
['fact' => "Palworld mixes Pokémon-style creatures with guns.", 'image_path' => 'images/facts/topazfacts.png'],

            // ...
            // Continue adding all facts up to 90 or more (I can help split or paginate if needed)
        ]);
    }
}
