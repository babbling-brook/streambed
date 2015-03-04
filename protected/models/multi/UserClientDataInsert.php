<?php
/**
 * Copyright 2015 Sky Wickenden
 * 
 * This file is part of StreamBed.
 * An implementation of the Babbling Brook Protocol.
 * 
 * StreamBed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * at your option any later version.
 * 
 * StreamBed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with StreamBed.  If not, see <http://www.gnu.org/licenses/>
 */


/**
 * Converts client user data  from JSON to DB and back.
 *
 * Uses a Nested Set to store the data in the DB.
 * See http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/
 * It is slightly addapted in that both a key and content are stored for every line.
 * If a line is just a container then the content will be null.
 * Also, each container is indexed by user_id, site_id (the client site) and client_key.
 *
 * @package PHP_Controllers
 */
class UserClientDataInsert
{

    /**
     * @var integer The id of the user that owns these client config settings.
     */
    private $user_id;

    /**
     * @var type The id of the client site that these config items are for.
     */
    private $site_id;

    /**
     * @var string The top level client key. Calculated from the depth_key.
     */
    private $client_key;

    /**
     * @var string The full key that points to the data that was passed in.
     */
    private $depth_key;

    /**
     * @var array The original key split into an array of key parts.
     */
    private $key_parts;

    /**
     * @var array The origional client data as passed into this class.
     */
    private $original_data;

    /**
     * @var array includes depth_key, lft and rgt values for each element in the tree.
     */
    private $final_data;

    /**
     * Makes space for a new tree of data to be inserted.
     *
     * @param integer $lft The left marker to start adding space from
     * @param integer $rgt The right marker to add space upto.
     * @param intger $space The amount of space to add.
     *
     * @return void
     */
    private function makeSpaceForMassInsert($lft, $rgt, $space) {
        $query = "
            UPDATE user_client_data
            SET
                rgt = rgt + :space,
                lft = lft + :space
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND client_key = :client_key
                AND rgt >= :rgt
                AND lft >= :lft;

            UPDATE user_client_data
            SET rgt = rgt + :space
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND client_key = :client_key
                AND rgt >= :rgt
                AND lft <= :lft";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $this->user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $this->site_id, PDO::PARAM_INT);
        $command->bindValue(":client_key", $this->client_key, PDO::PARAM_STR);
        $command->bindValue(":lft", $lft, PDO::PARAM_INT);
        $command->bindValue(":rgt", $rgt, PDO::PARAM_INT);
        $command->bindValue(":space", $space, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Inserts a row of data.
     *
     * This needs to be called from the UserClientDataInsert class!
     *
     * @return true|string True or an error message.
     */
    private function saveRowToDB($depth_key, $lft, $rgt, $type, $data=null) {
        $row = new UserClientData;
        $row->user_id = $this->user_id;
        $row->site_id = $this->site_id;
        $row->client_key = $this->client_key;
        $row->depth_key = $depth_key;
        $row->data_type = $type;
        $row->data = $data;
        $row->lft = $lft;
        $row->rgt = $rgt;

        $result = $row->save();
        if ($result === false) {
            return 'Error saving user client data :' . ErrorHelper::model($row->getErrors());
        } else {
            return true;
        }
    }

    /**
     * Iterates through $this->final_data, inserting each row into the DB.
     *
     * @return void
     */
    private function insertRows($row) {
        $data = null;
        $type = 'object';
        if (is_array($row['data']) === false) {
            $data = $row['data'];
            $type = gettype($data);
            if ($data === false) {
                $data = 'false';
            } else if ($data === true) {
                $data = 'true';
            }
        }
        $result = $this->saveRowToDB($row['depth_key'], $row['lft'], $row['rgt'], $type, $data);
        if ($result !== true) {
            return $result;
        }
        if (is_array($row['data']) === true) {
            foreach ($row['data'] as $next_item) {
                $result = $this->insertRows($next_item);
                if ($result !== true) {
                    return $result;
                }
            }
        }
        return $result;
    }

    /**
     * Deletes all rows between lft and rgt.
     *
     * Also updates the rgt index of all those after rgt and the ancestors
     * This needs to be called from the UserClientDataInsert class!
     *
     * @param integer $lft The left boundary of the rows to delete in the nested set.
     * @param integer $rgt The right boundary of the rows to delete in the nested set.
     *
     * @return void
     */
    private function deleteRows($lft, $rgt) {
        $query = "
            DELETE FROM user_client_data
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND client_key = :client_key
                AND lft >= :lft
                AND rgt <= :rgt;

            UPDATE user_client_data
            SET rgt = rgt - :difference
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND client_key = :client_key
                AND rgt > :rgt
                AND lft < :lft;

            UPDATE user_client_data
            SET
                rgt = rgt - :difference,
                lft = lft - :difference
            WHERE
                user_id = :user_id
                AND site_id = :site_id
                AND client_key = :client_key
                AND rgt > :rgt
                AND lft > :lft";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":user_id", $this->user_id, PDO::PARAM_INT);
        $command->bindValue(":site_id", $this->site_id, PDO::PARAM_INT);
        $command->bindValue(":client_key", $this->client_key, PDO::PARAM_STR);
        $command->bindValue(":lft", $lft, PDO::PARAM_INT);
        $command->bindValue(":rgt", $rgt, PDO::PARAM_INT);
        $difference = $rgt - $lft;
        $command->bindValue(":difference", $difference, PDO::PARAM_INT);
        $command->execute();
    }

    /**
     * Iterates through the tree recursively adding left and right boundaries as it goes.
     *
     * It also moves the row contents into a 'data' key in order to make room for the lft and rgt values.
     *
     * @param type $row
     * @param string $key_so_far
     * @param type $depth
     * @return type
     */
    private function calculateLeftAndRgt($original_row, &$final_row, $depth_key, $lft) {
        $final_row = array();
        $final_row['data'] = $original_row;
        $final_row['lft'] = $lft;
        $final_row['depth_key'] = $depth_key;

        $rgt = $lft + 1;
        $lft = $rgt;
        if (is_array($original_row) === true && count($original_row) > 0) {
            foreach ($final_row['data'] as $item_key => &$item) {
                $lft++;
                $new_depth_key = $depth_key . '.' . $item_key;
                $new_rgt = $this->calculateLeftAndRgt($item, $item, $new_depth_key, $lft);
                $lft = $new_rgt;
            }
            $rgt = $new_rgt + 1;
        }
        $final_row['rgt'] = $rgt;
        return $rgt;
    }

    /**
     *
     */
    private function calculateClientKeyFromDepthKey() {
        if (strpos($this->depth_key, '.') === false) {
            $this->client_key = $this->depth_key;
        } else {
            $this->client_key = substr($this->depth_key, 0, strpos($this->depth_key, '.'));
        }
    }

    /**
     * Removes all rows from the DB table that the new data is replacing.
     *
     * @return void
     */
    private function removeStaleData() {
        $row = UserClientData::getRow($this->user_id, $this->site_id, $this->client_key, $this->depth_key);
        if ($row === false) {
            return;
        }

        $this->deleteRows(
            $row['lft'],
            $row['rgt']
        );
    }

    /**
     * Fetches the parent row for the current tree.
     *
     * @return array A row of parent data.
     */
    private function getParentRow() {
        $parent_key_parts = $this->key_parts;
        array_pop($parent_key_parts);
        $new_partial_depth_key = implode('.', $parent_key_parts);
        $parent_row = UserClientData::getRow(
            $this->user_id,
            $this->site_id,
            $this->client_key,
            $new_partial_depth_key
        );
        return $parent_row;
    }

    /**
     * Recursively adapts the data so that includes any ancestors that do not exist in the DB.
     *
     * @return void
     */
    private function makeMissingParents() {
        // When the top ancestor is reached, then escape the process.
        if (count($this->key_parts) === 1) {
            return;

        // Otherwise check if the parent exists.
        } else {
            $parent_key_parts = $this->key_parts;
            $last_key = array_pop($parent_key_parts);
            $new_partial_depth_key = implode('.', $parent_key_parts);
            $parent_row = UserClientData::getRow(
                $this->user_id,
                $this->site_id,
                $this->client_key,
                $new_partial_depth_key
            );
            // Add the parent to the data so that it is also inserted. Then check for its grandparent.
            if ($parent_row === false) {
                $this->depth_key = $new_partial_depth_key;
                $this->original_data = array($last_key => $this->original_data);
                $this->key_parts = $parent_key_parts;
                makeMissingParents();
            }
        }
    }

    /**
     *
     * @param type $user_id
     * @param type $site_id
     * @param type $depth_key
     */
    public function deleteData($user_id, $site_id, $depth_key) {
        $this->user_id = $user_id;
        $this->site_id = $site_id;
        $this->depth_key = $depth_key;
        $this->key_parts = explode('.', $this->depth_key);
        $this->calculateClientKeyFromDepthKey();
        $this->removeStaleData();
    }

    /**
     *
     * @param type $user_id
     * @param type $site_id
     * @param type $data
     * @param type $depth_key
     * @return type
     */
    public function storeData($user_id, $site_id, $data, $depth_key) {
        $this->user_id = $user_id;
        $this->site_id = $site_id;
        $this->depth_key = $depth_key;
        $this->key_parts = explode('.', $this->depth_key);
        $this->original_data = $data;
        $this->calculateClientKeyFromDepthKey();

        $this->removeStaleData();
        $this->makeMissingParents($depth_key);

        if ($this->depth_key !== $this->client_key) {
            $parent_row = $this->getParentRow();
            $lft = $parent_row['rgt'] - 1;
        } else {
            $lft = 0;
        }

        $rgt = $this->calculateLeftAndRgt($this->original_data, $this->final_data, $depth_key, $lft);
        $new_entries_count = $rgt - $lft + 1;

        $this->makeSpaceForMassInsert($lft, $rgt, $new_entries_count);
        $result = $this->insertRows($this->final_data);
        return $result;
    }
}

?>
