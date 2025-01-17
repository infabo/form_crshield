#!/bin/sh

mysqldump --no-data -u$DB_USERNAME -p$DB_PASSWORD typo3_formcrshield_acceptance_v11 > typo3.sql
mysqldump --no-create-info \
          --ignore-table=typo3_formcrshield_acceptance_v11.sys_log \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_hash \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_hash_tags \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_pages \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_pages_tags \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_imagesizes \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_imagesizes_tags \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_pagesection \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_pagesection_tags \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_rootline \
          --ignore-table=typo3_formcrshield_acceptance_v11.cache_rootline_tags \
          --ignore-table=typo3_formcrshield_acceptance_v11.tx_extensionmanager_domain_model_extension	 \
          -u$DB_USERNAME -p$DB_PASSWORD typo3_formcrshield_acceptance_v11 >> typo3.sql
