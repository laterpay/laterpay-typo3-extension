CREATE TABLE tt_laterpay_terms_price (
    id                INT(11)            NOT NULL AUTO_INCREMENT,
    term_id           INT(11)            NOT NULL,
    price             DOUBLE             NOT NULL DEFAULT '0',
    revenue_model     ENUM('ppu', 'sis') NOT NULL DEFAULT 'ppu',
    PRIMARY KEY  (id),
) ENGINE=InnoDB;


CREATE TABLE tt_laterpay_payment_history (
    id                INT(11)              NOT NULL AUTO_INCREMENT,
    mode              ENUM('test', 'live') NOT NULL DEFAULT 'test',
    post_id           INT(11)              NOT NULL DEFAULT 0,
    currency_id       INT(11)              NOT NULL,
    price             FLOAT                NOT NULL,
    date              DATETIME             NOT NULL,
    ip                INT                  NOT NULL,
    hash              VARCHAR(32)          NOT NULL,
    revenue_model     ENUM('ppu', 'sis')   NOT NULL DEFAULT 'ppu',
    pass_id           INT(11)              NOT NULL DEFAULT 0,
    code              VARCHAR(6)           NULL DEFAULT NULL,
    PRIMARY KEY  (id),
    UNIQUE KEY  (mode, hash),
) ENGINE=InnoDB;

CREATE TABLE tt_laterpay_post_views (
    post_id           INT(11)         NOT NULL,
    date              DATETIME        NOT NULL,
    user_id           VARCHAR(32)     NOT NULL,
    count             BIGINT UNSIGNED NOT NULL DEFAULT 1,
    ip                VARBINARY(16)   NOT NULL,
    UNIQUE KEY  (post_id, user_id),
) ENGINE=InnoDB;

CREATE TABLE tt_laterpay_passes (
    pass_id           INT(11)       NOT NULL AUTO_INCREMENT,
    duration          INT(11)       NULL DEFAULT NULL,
    period            INT(11)       NULL DEFAULT NULL,
    access_to         INT(11)       NULL DEFAULT NULL,
    access_category   BIGINT(20)    NULL DEFAULT NULL,
    price             DECIMAL(10,2) NULL DEFAULT NULL,
    revenue_model     VARCHAR(12)   NULL DEFAULT NULL,
    title             VARCHAR(255)  NULL DEFAULT NULL,
    description       VARCHAR(255)  NULL DEFAULT NULL,
    PRIMARY KEY (pass_id),
    INDEX access_to (access_to),
    INDEX period (period),
    INDEX duration (duration),
) ENGINE=InnoDB;

CREATE TABLE `tt_content` (
    laterpay_teaser  TEXT           NULL        DEFAULT NULL,
    laterpay_price   decimal(10,2)  NOT NULL    DEFAULT 0,
) ENGINE=InnoDB;

