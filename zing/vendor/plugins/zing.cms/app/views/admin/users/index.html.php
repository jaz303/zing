<table class='table-list'>
  <thead>
    <tr>
      <th style='text-align:center'>Type</th>
      <th>Name</th>
      <th>Username</th>
      <th>Email</th>
      <th class='actions'>Actions</th>
    </tr>
  </thead>
  <tbody>
    <? foreach ($users as $user) { ?>
      <tr>
        <td style='text-align:center'><?= icon('user_business', null, 'Administrator') ?></td>
        <td><?= h($user->get_surname()) ?>, <?= h($user->get_forename()) ?></td>
        <td><?= h($user->get_username()) ?></td>
        <td><a href='mailto:<?= h($user->get_email()) ?>'><?= h($user->get_email()) ?></a></td>
        <td class='actions'>
          <a href='<?= admin_url(':users/edit/' . $user->get_id()) ?>'><?= icon('pencil') ?> <span>Edit</span></a>
          <a href='<?= admin_url(':users/delete/' . $user->get_id()) ?>'
             data-method='post'
             data-confirm='Are you sure you wish to delete the user "<?= h($user->get_username()) ?>"?'>
            <?= icon('cross') ?> <span>Delete</span>
          </a>
        </td>
      </tr>
    <? } ?>
  </tbody>
</table>