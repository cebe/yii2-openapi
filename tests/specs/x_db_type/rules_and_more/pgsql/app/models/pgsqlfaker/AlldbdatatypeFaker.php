<?php
namespace app\models\pgsqlfaker;

use Faker\UniqueGenerator;
use app\models\pgsqlmodel\Alldbdatatype;

/**
 * Fake data generator for Alldbdatatype
 * @method static \app\models\pgsqlmodel\Alldbdatatype makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Alldbdatatype saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Alldbdatatype[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\pgsqlmodel\Alldbdatatype[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class AlldbdatatypeFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\pgsqlmodel\Alldbdatatype|\yii\db\ActiveRecord
     * @example
     *  $model = (new PostFaker())->generateModels(['author_id' => 1]);
     *  $model = (new PostFaker())->generateModels(function($model, $faker, $uniqueFaker) {
     *            $model->scenario = 'create';
     *            $model->author_id = 1;
     *            return $model;
     *  });
    **/
    public function generateModel($attributes = [])
    {
        $faker = $this->faker;
        $uniqueFaker = $this->uniqueFaker;
        $model = new \app\models\pgsqlmodel\Alldbdatatype();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->string_col = $faker->sentence;
        $model->varchar_col = $faker->sentence;
        $model->text_col = $faker->sentence;
        $model->text_col_array = [];
        $model->varchar_4_col = substr($faker->word(4), 0, 4);
        $model->varchar_5_col = substr($faker->text(5), 0, 5);
        $model->char_4_col = substr($faker->word(4), 0, 4);
        $model->char_5_col = $faker->sentence;
        $model->char_6_col = $faker->sentence;
        $model->char_7_col = substr($faker->text(6), 0, 6);
        $model->char_8_col = $faker->sentence;
        $model->decimal_col = $faker->randomFloat();
        $model->bit_col = $faker->numberBetween(0, 1000000);
        $model->bit_2 = $faker->numberBetween(0, 1000000);
        $model->bit_3 = $faker->numberBetween(0, 1000000);
        $model->ti = $faker->numberBetween(0, 1000000);
        $model->int2_col = $faker->numberBetween(0, 1000000);
        $model->smallserial_col = $faker->numberBetween(0, 1000000);
        $model->serial2_col = $faker->numberBetween(0, 1000000);
        $model->si_col = $faker->numberBetween(0, 1000000);
        $model->si_col_2 = $faker->numberBetween(0, 1000000);
        $model->bi = $faker->numberBetween(0, 1000000);
        $model->bi2 = $faker->numberBetween(0, 1000000);
        $model->int4_col = $faker->numberBetween(0, 1000000);
        $model->bigserial_col = $faker->numberBetween(0, 1000000);
        $model->bigserial_col_2 = $faker->numberBetween(0, 1000000);
        $model->int_col = $faker->numberBetween(0, 1000000);
        $model->int_col_2 = $faker->numberBetween(0, 1000000);
        $model->numeric_col = $faker->randomFloat();
        $model->numeric_col_2 = $faker->randomFloat();
        $model->numeric_col_3 = $faker->randomFloat();
        $model->double_p_2 = $faker->randomFloat();
        $model->double_p_3 = $faker->randomFloat();
        $model->real_col = $faker->randomFloat();
        $model->float4_col = $faker->randomFloat();
        $model->date_col = $faker->dateTimeThisCentury->format('Y-m-d');
        $model->time_col = $faker->time('H:i:s');
        $model->time_col_2 = $faker->sentence;
        $model->time_col_3 = $faker->sentence;
        $model->time_col_4 = substr($faker->word(3), 0, 3);
        $model->timetz_col = $faker->sentence;
        $model->timetz_col_2 = substr($faker->word(3), 0, 3);
        $model->timestamp_col = $faker->dateTimeThisYear('now', 'UTC')->format('Y-m-d H:i:s');
        $model->timestamp_col_2 = $faker->unixTime;
        $model->timestamp_col_3 = $faker->unixTime;
        $model->timestamp_col_4 = substr($faker->unixTime, 0, 3);
        $model->timestamptz_col = $faker->unixTime;
        $model->timestamptz_col_2 = substr($faker->unixTime, 0, 3);
        $model->date2 = $faker->dateTimeThisCentury->format('Y-m-d');
        $model->timestamp_col_z = $faker->dateTimeThisYear('now', 'UTC')->format('Y-m-d H:i:s');
        $model->bit_varying = $faker->numberBetween(0, 1000000);
        $model->bit_varying_n = $faker->numberBetween(0, 1000000);
        $model->bit_varying_n_2 = $faker->numberBetween(0, 1000000);
        $model->bit_varying_n_3 = $faker->numberBetween(0, 1000000);
        $model->box_col = $faker->sentence;
        $model->character_col = $faker->sentence;
        $model->character_n = substr($faker->text(12), 0, 12);
        $model->character_varying = $faker->sentence;
        $model->character_varying_n = substr($faker->text(12), 0, 12);
        $model->json_col = [];
        $model->jsonb_col = [];
        $model->json_col_def = [];
        $model->json_col_def_2 = [];
        $model->text_def = $faker->sentence;
        $model->json_def = [];
        $model->jsonb_def = [];
        $model->cidr_col = $faker->sentence;
        $model->circle_col = $faker->sentence;
        $model->date_col_z = $faker->dateTimeThisCentury->format('Y-m-d');
        $model->float8_col = $faker->randomFloat();
        $model->inet_col = $faker->sentence;
        $model->interval_col = $faker->sentence;
        $model->interval_col_2 = $faker->sentence;
        $model->interval_col_3 = substr($faker->word(3), 0, 3);
        $model->line_col = $faker->sentence;
        $model->lseg_col = $faker->sentence;
        $model->macaddr_col = $faker->sentence;
        $model->money_col = $faker->sentence;
        $model->path_col = $faker->sentence;
        $model->pg_lsn_col = $faker->numberBetween(0, 1000000);
        $model->point_col = $faker->sentence;
        $model->polygon_col = $faker->sentence;
        $model->serial_col = $faker->numberBetween(0, 1000000);
        $model->serial4_col = $faker->numberBetween(0, 1000000);
        $model->tsquery_col = $faker->sentence;
        $model->tsvector_col = $faker->sentence;
        $model->txid_snapshot_col = $faker->sentence;
        $model->uuid_col = $faker->sentence;
        $model->xml_col = $faker->sentence;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
