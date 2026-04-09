<?php



require_once __DIR__ . '/BaseModel.php';


class Role extends BaseModel implements JsonSerializable
{
    public static function create(string $name, bool $p_log, bool $p_boutique, bool $p_reunion, bool $p_utilisateur, bool $p_grade, bool $p_role, bool $p_actualite, bool $p_evenement, bool $p_comptabilite, bool $p_achat, bool $p_moderation): Role
    {
        $DB = new DB();

        $id = $DB->query("INSERT INTO ROLE (nom_role, p_log_role, p_boutique_role, p_reunion_role, p_utilisateur_role, p_grade_role, p_roles_role, p_actualite_role, p_evenements_role, p_comptabilite_role, p_achats_role, p_moderation_role)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", "siiiiiiiiiii", [$name, $p_log, $p_boutique, $p_reunion, $p_utilisateur, $p_grade, $p_role, $p_actualite, $p_evenement, $p_comptabilite, $p_achat, $p_moderation]);

        return new Role($id);
    }


    public function update(string $name, bool $p_log, bool $p_boutique, bool $p_reunion, bool $p_utilisateur, bool $p_grade, bool $p_role, bool $p_actualite, bool $p_evenement, bool $p_comptabilite, bool $p_achat, bool $p_moderation): Role
    {
        $this->DB->query("UPDATE ROLE SET nom_role = ?, p_log_role = ?, p_boutique_role = ?, p_reunion_role = ?, p_utilisateur_role = ?, p_grade_role = ?, p_roles_role = ?, p_actualite_role = ?, p_evenements_role = ?, p_comptabilite_role = ?, p_achats_role = ?, p_moderation_role = ? WHERE id_role = ?", "siiiiiiiiiiii", [$name, $p_log, $p_boutique, $p_reunion, $p_utilisateur, $p_grade, $p_role, $p_actualite, $p_evenement, $p_comptabilite, $p_achat, $p_moderation, $this->id]);

        return $this;
    }

    public function delete(): void
    {
        // Désassocie tous les membres de ce rôle
        // Puis supprime le rôle
        $this->DB->query("DELETE FROM ASSIGNATION WHERE id_role = ?", "i", [$this->id]);
        $this->DB->query("DELETE FROM ROLE WHERE id_role = ?", "i", [$this->id]);
    }

    public static function getInstance($id): ?Role
    {
        $DB = new DB();
        $result = $DB->select("SELECT * FROM ROLE WHERE id_role = ?", "i", [$id]);

        if (count($result) == 0) {
            return null;
        }

        return new Role($id);
    }

    /**
     * @return Member[]
     */
    public function getMembers(): array
    {
        $result = $this->DB->select("SELECT MEMBRE.*
                               FROM MEMBRE
                               INNER JOIN ASSIGNATION
                               ON MEMBRE.id_membre = ASSIGNATION.id_membre
                               WHERE ASSIGNATION.id_role = ?", "i", [$this->id]);

        $members = [];
        foreach ($result as $member) {
            $members[] = Member::getInstance($member['id_membre']);
        }

        return $members;
    }

    public function toJson(): array
    {
        $data = $this->DB->select(
            "SELECT id_role, nom_role,
                p_log_role          AS p_log,
                p_boutique_role     AS p_boutique,
                p_reunion_role      AS p_reunion,
                p_utilisateur_role  AS p_utilisateur,
                p_grade_role        AS p_grade,
                p_roles_role        AS p_role,
                p_actualite_role    AS p_actualite,
                p_evenements_role   AS p_evenement,
                p_comptabilite_role AS p_comptabilite,
                p_achats_role       AS p_achat,
                p_moderation_role   AS p_moderation
             FROM ROLE WHERE id_role = ?", "i", [$this->id]);

        return $data[0];
    }

    public static function bulkFetch()
    {
        $DB = new DB();
        $result = $DB->select("SELECT id_role, nom_role FROM ROLE");

        return $result;
    }

    public function addMember(Member $member): void
    {
        $this->DB->query("INSERT INTO ASSIGNATION (id_membre, id_role) VALUES (?, ?)", "ii", [$member->id, $this->id]);
    }

    public function __jsonSerialize(): array
    {
        return $this->toJson();
    }

    public function __toString()
    {
        return json_encode($this->toJson());
    }

    // Permet de sérialiser l'objet en JSON
    public function jsonSerialize(): array
    {
        return $this->toJson();
    }
}
