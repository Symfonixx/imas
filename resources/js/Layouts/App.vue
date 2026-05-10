<template>
    <div id="wrapper">
        <UserNavbar/>
        <div class="clearfix"></div>
        <slot/>
        <UserFooter/>
    </div>
</template>

<script setup>
import UserNavbar from '@/Layouts/Findhouses/UserNavbar.vue';
import UserFooter from '@/Layouts/Findhouses/UserFooter.vue';
</script>
