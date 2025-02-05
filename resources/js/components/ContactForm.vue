<script>
import { ref } from "vue";
import { useForm, useField } from "vee-validate";
import * as yup from "yup";

const schema = yup.object({
    name: yup.string().min(2).max(10).required(),
    email: yup
        .string()
        .email()
        .test("domain-check", "Gmail emails are not allowed", (value) => {
            if (!value) return false;
            const domain = value.split("@")[1];
            return domain !== "gmail.com";
        })
        .required(),
    phone: yup
        .string()
        .matches(/^\+\d{1,3}\d{7,}$/g, "Invalid phone number")
        .required(),
    message: yup.string().min(10).required(),
    street: yup.string().required(),
    state: yup.string().required(),
    zip: yup.string().required(),
    country: yup.string().required(),
    images: yup.mixed().test("fileType", "Only JPG images allowed", (value) => {
        return value && value.length
            ? value.every((file) => file.type === "image/jpeg")
            : false;
    }),
    files: yup.mixed().test("fileType", "Only PDF files allowed", (value) => {
        return value && value.length
            ? value.every((file) => file.type === "application/pdf")
            : false;
    }),
});

export default {
    setup() {
        const { handleSubmit, errors } = useForm({ validationSchema: schema });
        const name = useField("name");
        const email = useField("email");
        const phone = useField("phone");
        const message = useField("message");
        const street = useField("street");
        const state = useField("state");
        const zip = useField("zip");
        const country = useField("country");
        const images = useField("images");
        const files = useField("files");

        const onSubmit = handleSubmit(async (values) => {
            const formData = new FormData();
            Object.keys(values).forEach((key) => {
                if (key === "images" || key === "files") {
                    values[key].forEach((file) => formData.append(key, file));
                } else {
                    formData.append(key, values[key]);
                }
            });

            try {
                const response = await fetch("/api/submissions", {
                    method: "POST",
                    body: formData,
                });
                if (!response.ok) throw new Error("Failed to submit");
                alert("Form submitted successfully!");
            } catch (error) {
                console.error(error);
                alert("Submission failed");
            }
        });

        return {
            name,
            email,
            phone,
            message,
            street,
            state,
            zip,
            country,
            images,
            files,
            errors,
            onSubmit,
        };
    },
};
</script>

<template>
    <form
        @submit.prevent="onSubmit"
        class="container mt-4 p-4 border rounded shadow-sm"
    >
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input v-model="name.value" type="text" class="form-control" />
            <span class="text-danger">{{ errors.name }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input v-model="email.value" type="email" class="form-control" />
            <span class="text-danger">{{ errors.email }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input v-model="phone.value" type="text" class="form-control" />
            <span class="text-danger">{{ errors.phone }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea v-model="message.value" class="form-control"></textarea>
            <span class="text-danger">{{ errors.message }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">Street</label>
            <input v-model="street.value" type="text" class="form-control" />
            <span class="text-danger">{{ errors.street }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">State</label>
            <input v-model="state.value" type="text" class="form-control" />
            <span class="text-danger">{{ errors.state }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">Zip</label>
            <input v-model="zip.value" type="text" class="form-control" />
            <span class="text-danger">{{ errors.zip }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">Country</label>
            <input v-model="country.value" type="text" class="form-control" />
            <span class="text-danger">{{ errors.country }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">Images (JPG only)</label>
            <input
                type="file"
                class="form-control"
                @change="(e) => (images.value = [...e.target.files])"
                accept="image/jpeg"
                multiple
            />
            <span class="text-danger">{{ errors.images }}</span>
        </div>
        <div class="mb-3">
            <label class="form-label">Files (PDF only)</label>
            <input
                type="file"
                class="form-control"
                @change="(e) => (files.value = [...e.target.files])"
                accept="application/pdf"
                multiple
            />
            <span class="text-danger">{{ errors.files }}</span>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</template>
