SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `start_date` bigint(20) NOT NULL,
  `name` text NOT NULL,
  `short_name` text NOT NULL,
  `completed` tinyint(1) NOT NULL,
  `team_one_id` int(11) DEFAULT NULL,
  `team_one_score` int(11) DEFAULT '0',
  `team_two_id` int(11) DEFAULT NULL,
  `team_two_score` int(11) DEFAULT '0',
  `winner` int(11) DEFAULT NULL,
  `week` tinyint(4) NOT NULL,
  `tie` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `events`
--
DELIMITER $$
CREATE TRIGGER `check_tie` BEFORE UPDATE ON `events` FOR EACH ROW BEGIN
 IF NEW.completed IS TRUE AND NEW.winner IS NULL THEN 
 SET NEW.tie = TRUE;
 END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_leaderboard` AFTER UPDATE ON `events` FOR EACH ROW BEGIN
		IF NEW.completed = 1 THEN
    		INSERT INTO
            	point_additives (entry_id, score, user_id, week)
            	SELECT 
                 	entry_id, confidence, user_id, week 
                 FROM 
                 	user_entries
                 WHERE
                 	event_id = NEW.event_id AND
                 	winner_id = NEW.winner AND
                 	week = NEW.week;
        END IF;
    END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_team_stats` AFTER UPDATE ON `events` FOR EACH ROW BEGIN # there is a better way of doing this but im lazy
  IF NEW.winner IS NOT NULL THEN
    IF OLD.`team_one_id` = NEW.winner THEN
        UPDATE `teams`  # team one won, so add 1 loss to two and one win to one
          SET
            `losses` = `losses` + 1
          WHERE
          	`team_id` = OLD.team_two_id;
        UPDATE `teams`
          SET
            `wins` = `wins` + 1
          WHERE
          	`team_id` = OLD.team_one_id;
    ELSE # team two won, increment team one losse and team two wins
    	UPDATE `teams`
          SET
            `losses` = `losses` + 1
          WHERE
          	`team_id` = OLD.team_one_id;
        UPDATE `teams`
          SET
            `wins` = `wins` + 1
          WHERE
          	`team_id` = OLD.team_two_id;
    END IF;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `last_updated`
--

CREATE TABLE `last_updated` (
  `id` tinyint(4) NOT NULL,
  `time` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `point_additives`
--

CREATE TABLE `point_additives` (
  `user_id` tinyint(4) NOT NULL,
  `score` int(11) DEFAULT '0',
  `week` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `stats`
-- (See below for the actual view)
--
CREATE TABLE `stats` (
`entry_id` int(11)
,`event_id` int(11)
,`confidence` int(11)
,`winner_id` int(11)
,`week` tinyint(4)
,`username` varchar(512)
,`completed` int(4)
,`correct` int(1)
);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `slug` text NOT NULL,
  `location` text NOT NULL,
  `display_name` text NOT NULL,
  `short_display_name` text NOT NULL,
  `abbreviation` text NOT NULL,
  `color` text NOT NULL,
  `alternate_color` text NOT NULL,
  `wins` int(11) NOT NULL DEFAULT '0',
  `losses` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` tinyint(4) NOT NULL,
  `username` varchar(512) NOT NULL,
  `password_hash` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_entries`
--

CREATE TABLE `user_entries` (
  `entry_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `confidence` int(11) NOT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `week` tinyint(4) NOT NULL,
  `user_id` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `stats`
--
DROP TABLE IF EXISTS `stats`;

CREATE VIEW `stats` AS SELECT `UE`.`entry_id` AS `entry_id`, `UE`.`event_id` AS `event_id`, `UE`.`confidence` AS `confidence`, `UE`.`winner_id` AS `winner_id`, `UE`.`week` AS `week`, (select `U`.`username` from `users` `U` where (`U`.`user_id` = `UE`.`user_id`)) AS `username`, (select `E`.`completed` from `events` `E` where (`E`.`event_id` = `UE`.`event_id`)) AS `completed`, (select exists(select 1 from `point_additives` `P` where (`P`.`entry_id` = `UE`.`entry_id`))) AS `correct` FROM `user_entries` AS `UE``UE` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `team_one_id` (`team_one_id`),
  ADD KEY `team_two_id` (`team_two_id`),
  ADD KEY `winner` (`winner`);

--
-- Indexes for table `last_updated`
--
ALTER TABLE `last_updated`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `point_additives`
--
ALTER TABLE `point_additives`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `entry_id` (`entry_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_entries`
--
ALTER TABLE `user_entries`
  ADD PRIMARY KEY (`entry_id`),
  ADD UNIQUE KEY `unique_index` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `winner_id` (`winner_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_entries`
--
ALTER TABLE `user_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`team_one_id`) REFERENCES `teams` (`team_id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`team_two_id`) REFERENCES `teams` (`team_id`),
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`winner`) REFERENCES `teams` (`team_id`);

--
-- Constraints for table `point_additives`
--
ALTER TABLE `point_additives`
  ADD CONSTRAINT `point_additives_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `point_additives_ibfk_2` FOREIGN KEY (`entry_id`) REFERENCES `user_entries` (`entry_id`);

--
-- Constraints for table `user_entries`
--
ALTER TABLE `user_entries`
  ADD CONSTRAINT `user_entries_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `user_entries_ibfk_3` FOREIGN KEY (`winner_id`) REFERENCES `teams` (`team_id`),
  ADD CONSTRAINT `user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;