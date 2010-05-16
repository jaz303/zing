<h1>This is the test view <?= h('foo') ?></h1>

<h2>Bleem</h2>

<?= c("#bleem.foo.bar", ^{
    return c('b', 'not all my tricks rookie!');
}); ?>

<?= i('zing.png') ?>

<?= text_input('foo', 'hello') ?>

<?= textarea('raaa', 'Here is some content to edit') ?>