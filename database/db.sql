CREATE TABLE `user_center`.`account` (
  `account_id`    BIGINT(20)  NOT NULL,
  `user_key`      VARCHAR(45) NULL,
  `password`      VARCHAR(45) NULL,
  `account_type`  TINYINT(5)  NULL,
  `union_user_id` BIGINT(20)  NULL,
  `status`        TINYINT(5)  NULL,
  PRIMARY KEY (`account_id`)
);


CREATE TABLE `user_center`.`user` (
  `user_id`     BIGINT(20)  NOT NULL,
  `telephone`   VARCHAR(45) NULL,
  `nickname`    VARCHAR(45) NULL,
  `avatar`      VARCHAR(45) NULL,
  `birthday`    DATE        NULL,
  `sex`         TINYINT(5)  NULL,
  `signature`   VARCHAR(45) NULL,
  `user_source` VARCHAR(45) NULL,
  `role`        TINYINT(5)  NULL,
  `status`      TINYINT(5)  NULL,
  `create_time` DATETIME    NULL,
  `modify_time` DATETIME    NULL,
  PRIMARY KEY (`user_id`)
);


CREATE TABLE `user_center`.`channel` (
  `channel_id`       BIGINT(20)   NOT NULL,
  `channel_name`     VARCHAR(45)  NULL,
  `channel_key`      VARCHAR(45)  NULL,
  `channel_secret`   VARCHAR(45)  NULL,
  `create_time`      DATETIME     NULL,
  `pay_callback_url` VARCHAR(100) NULL,
  `is_test`          TINYINT(5)   NULL,
  PRIMARY KEY (`channel_id`)
);


CREATE TABLE `user_center`.`mapping` (
  `mapping_id`  BIGINT(20)  NOT NULL,
  `channel_id`  BIGINT(20)  NULL,
  `channel_uid` VARCHAR(45) NULL,
  `user_id`     BIGINT(20)  NULL,
  `create_time` DATETIME    NULL,
  PRIMARY KEY (`mapping_id`)
);
