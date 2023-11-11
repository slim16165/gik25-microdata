<?php

namespace gik25microdata\Utility;

class UserHelper
{
//Limit the visibility of some post for specific users
//add_filter('parse_query', 'md_hide_others_roles_posts');
    function md_hide_others_roles_posts($query)
    {
        global $pagenow;

        //if user is not logged exit
        if (!is_user_logged_in())
            return;

        $limited_users = array(/*'indexo3', */
            'Gerardatt', 'GiuseppeAmbrosio', 'SaraMarchiano');
        $authors_post_to_hide = array(/*4,*/
            7 /* 'Gianluigi Salvi'*/); //TODO: finish to implement the array version

        $current_user = wp_get_current_user();

        if (!in_array($current_user->nickname, $limited_users))
            return;

        list($users, $author__in) = GetAllUsersButExcluded($authors_post_to_hide);

        if (count($users))
        {
            if ($pagenow == 'edit.php')
            {
                $query->query_vars['author__in'] = $author__in;
            }
        }
    }

    function GetAllUsersButExcluded(array $authors_post_to_hide): array
    {
        $user_args = [
            'fields ' => 'ID',
            'exclude' => $authors_post_to_hide
        ];
        $users = get_users($user_args);

        $author__in = [];
        foreach ($users as $user)
        {
            $author__in[] = $user->ID;
        }
        return array($users, $author__in);
    }
}