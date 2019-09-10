<?php

namespace LdapRecord\Tests\Models;

use Carbon\Carbon;
use LdapRecord\Utilities;
use LdapRecord\Models\Model;
use LdapRecord\Tests\TestCase;

class ModelAccessorTest extends TestCase
{
    public function test_model_uses_accessor()
    {
        $model = new ModelAccessorStub();

        $this->assertEquals(['bar'], $model->getAttributes()['foo']);
        $this->assertEquals(['bar'], $model->jsonSerialize()['foo']);
        $this->assertEquals('barbaz', $model->foo);
        $this->assertEquals('barbaz', $model->getAttribute('foo'));
        $this->assertTrue(isset($model->foo));
    }

    public function test_model_uses_accessor_with_hyphen()
    {
        $model = new ModelAccessorStub();

        $this->assertEquals('baz-other', $model->getAttribute('foo-bar'));
        $this->assertEquals(['baz'], $model->jsonSerialize()['foo-bar']);
        $this->assertEquals('baz-other', $model->foo_bar);
        $this->assertNull($model->foobar);
        $this->assertNull($model->getAttribute('foobar'));
    }

    public function test_model_uses_mutator()
    {
        $model = new ModelMutatorStub();

        $model->foo = 'setter-';

        $this->assertEquals(['setter-baz'], $model->foo);
        $this->assertEquals(['setter-baz'], $model->getAttributes()['foo']);
        $this->assertEquals(['setter-baz'], $model->jsonSerialize()['foo']);
        $this->assertTrue(isset($model->foo));
    }

    public function test_models_uses_mutator_with_hypen()
    {
        $model = new ModelMutatorStub();

        $model->foo_bar = 'setter';

        $this->assertEquals(['setter-other'], $model->foo_bar);
        $this->assertEquals(['setter-other'], $model->getAttributes()['foo-bar']);
        $this->assertEquals('setter-other', $model->getFirstAttribute('foo-bar'));
        $this->assertTrue(isset($model->foo_bar));
        $this->assertNull($model->foobar);
        $this->assertNull($model->getAttribute('foobar'));
    }

    public function test_models_mutate_dates_to_ldap_type()
    {
        $model = new ModelDateMutatorStub();
        $date = new Carbon();

        $model->createtimestamp = $date;

        // Case insensitivity
        $this->assertInstanceOf(Carbon::class, $model->createTimestamp);
        $this->assertEquals($date->setTimezone('UTC')->micro(0), $model->createtimestamp);
        $this->assertEquals($date->format('YmdHis\Z'), $model->getAttributes()['createtimestamp'][0]);
    }

    public function test_models_mutate_dates_to_windows_type()
    {
        $model = new ModelDateMutatorStub();
        $date = new Carbon();

        $model->whenchanged = $date;

        // Case insensitivity
        $this->assertInstanceOf(Carbon::class, $model->whenChanged);
        $this->assertEquals($date->setTimezone('UTC')->micro(0), $model->whenchanged);
        $this->assertEquals($date->format('YmdHis.0\Z'), $model->getAttributes()['whenchanged'][0]);
    }

    public function test_models_mutate_dates_to_windows_integer_type()
    {
        $model = new ModelDateMutatorStub();
        $date = new Carbon();

        $model->accountexpires = $date;

        // Case insensitivity
        $this->assertInstanceOf(Carbon::class, $model->accountExpires);
        $this->assertEquals($date->setTimezone('UTC')->micro(0), $model->accountexpires);
        $this->assertEquals(Utilities::convertUnixTimeToWindowsTime($date->getTimestamp()), $model->getAttributes()['accountexpires'][0]);
    }

    public function test_models_mutate_from_ldap_type_to_date()
    {
        $model = new ModelDateAccessorStub();

        $this->assertInstanceOf(Carbon::class, $model->createtimestamp);
        $this->assertInstanceOf(Carbon::class, $model->CreateTimestamp);
        $this->assertEquals('2019-09-10 22:02:04', $model->createtimestamp->toDateTimeString());

        $model->createTimestamp = $model->createTimestamp->addMinute();
        $this->assertEquals('2019-09-10 22:03:04', $model->createTimestamp->toDateTimeString());
    }

    public function test_models_mutate_from_windows_type_to_date()
    {
        $model = new ModelDateAccessorStub();

        $this->assertInstanceOf(Carbon::class, $model->whenchanged);
        $this->assertInstanceOf(Carbon::class, $model->WhenChanged);
        $this->assertEquals('2019-09-10 22:02:04', $model->whenchanged->toDateTimeString());

        $model->whenChanged = $model->whenChanged->addMinute();
        $this->assertEquals('2019-09-10 22:03:04', $model->whenchanged->toDateTimeString());
    }

    public function test_models_mutate_from_windows_integer_type_to_date()
    {
        $model = new ModelDateAccessorStub();

        $this->assertInstanceOf(Carbon::class, $model->accountexpires);
        $this->assertInstanceOf(Carbon::class, $model->AccountExpires);
        $this->assertEquals('2019-09-10 22:02:04', $model->accountexpires->toDateTimeString());

        $model->accountExpires = $model->accountExpires->addMinute();
        $this->assertEquals('2019-09-10 22:03:04', $model->accountExpires->toDateTimeString());
    }
}

class ModelAccessorStub extends Model
{
    protected $attributes = [
        'foo'     => ['bar'],
        'foo-bar' => ['baz'],
    ];

    public function getFooAttribute($bar)
    {
        return $bar[0].'baz';
    }

    public function getFooBarAttribute($baz)
    {
        return $baz[0].'-other';
    }
}

class ModelMutatorStub extends Model
{
    protected $attributes = [
        'foo'     => ['bar'],
        'foo-bar' => ['baz'],
    ];

    public function setFooAttribute($bar)
    {
        $this->attributes['foo'] = [$bar.'baz'];
    }

    public function setFooBarAttribute($baz)
    {
        $this->attributes['foo-bar'] = [$baz.'-other'];
    }
}

class ModelDateMutatorStub extends Model
{
    protected $dates = [
        'createTimestamp' => 'ldap',
        'whenchanged'     => 'windows',
        'accountexpires'  => 'windows-int',
    ];
}

class ModelDateAccessorStub extends ModelDateMutatorStub
{
    protected $attributes = [
        'createTimestamp' => ['20190910220204Z'],
        'whenchanged'     => ['20190910220204.0Z'],
        'accountexpires'  => ['132126265240000000'],
    ];
}
