<template>
<main class="container">

    <div class="tabs">
        <ul>
            <li v-for="t, key in tabs" :class="{'is-active': key == $route.params.tab}">
                <router-link :to="`/settings/${key}`">{{ t.label }}</router-link>
            </li>
        </ul>
    </div>

    <form @submit.prevent="saveSetting()">
        <template v-if="sets[$route.params.tab]">
            <b-table :data="sets[$route.params.tab]" :key="$route.params.tab" draggable>
                <b-table-column field="key" label="Ключ" width="180"
                    v-slot="props"
                >
                    <b-field :key="props.index">
                        <b-input required v-model="props.row.key"></b-input>
                    </b-field>
                </b-table-column>
                
                <b-table-column field="label" label="Название"
                    v-slot="props"
                >
                    <b-input v-model="props.row.label"></b-input>
                </b-table-column>

                <!-- <b-table-column :visible="$route.params.tab == 'statuses'"
                    field="mail" label="Письмо" width="70" centered
                    v-slot="props"
                >
                    <b-checkbox v-model="props.row.mail"></b-checkbox>
                </b-table-column> -->

                <b-table-column :visible="$route.params.tab == 'statuses'"
                    field="color" label="Цвет" width="70" centered
                    v-slot="props"
                >
                    <b-input type="color" v-model="props.row.color"></b-input>
                </b-table-column>
                
                <b-table-column custom-key="delete" width="70" centered
                    v-slot="props"
                >
                    <a @click="remove(props.index)" class="delete"></a>
                </b-table-column>
            </b-table>
            <div class="level"></div>
        </template>

        <div class="control">
            <button @click.prevent="addRow()" class="button">+</button>
            <b-button icon-left="content-save" type="is-primary" :loading="loading" native-type="submit">Сохранить</b-button>
        </div>
    
    </form>


</main>
</template>

<script src="./settings.js"></script>